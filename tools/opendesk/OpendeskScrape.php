<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Skraper za opendesk.cc — javni katalog open-design nameštaja (CC license).
 *
 * Strategija:
 *   1) Fetchuje /designs listing → ekstraktuje slug-ove iz href-ova.
 *   2) Za svaki design fetchuje /<parent>/<child> i parsira HTML:
 *      - <h1>Name</h1>
 *      - Cloudinary slike (res.cloudinary.com/dfsllxsnj)
 *      - Description: prvi smisleni <p> nakon h1
 *      - Designer: iz <h2> "Designed by ..." ili FurnitureBlocksDesignedby blok
 *   3) Kategorija po heuristici iz naziva:
 *      table/desk → Stolovi; chair/stool/bench → Stolice;
 *      bookshelf/locker/pedestal → Skladištenje
 *   4) Cena: per-category mid-range RSD, deterministic preko hash(slug)
 *   5) Slike download u storage/app/public/products/opendesk/<slug>/img-N.jpg
 *   6) Output: .data/opendesk.json (kompatibilan sa OpendeskSeeder-om)
 *
 * Etička napomena: Opendesk dizajni su deljeni pod CC license; cene postavljamo sami,
 * attribution stoji u footer-u storefronta. User-Agent identifikuje skripta.
 */
class OpendeskScrape extends Command
{
    protected $signature = 'opendesk:scrape
                            {--limit=25 : Maksimalan broj proizvoda}
                            {--no-images : Preskoči download slika}
                            {--delay=1 : Delay (sekunde) između HTTP poziva}';

    protected $description = 'Skida katalog sa opendesk.cc i sprema u .data/opendesk.json + storage/app/public/products/opendesk/.';

    private const BASE_URL = 'https://opendesk.cc';
    private const USER_AGENT = 'sloj-kolektiv-import/0.1 (+mailto:info@slojkolektiv.rs)';
    private const CLOUDINARY_PATTERN = '#https://res\.cloudinary\.com/dfsllxsnj/image/upload/[^\s"\']+\.(jpg|jpeg|png|webp)#i';

    /** Mapiranje engleski naziv → srpska kategorija. Order je važan. */
    private const CATEGORY_RULES = [
        'desk'      => 'Stolovi',
        'table'     => 'Stolovi',
        'pedestal'  => 'Skladištenje',
        'bookshelf' => 'Skladištenje',
        'locker'    => 'Skladištenje',
        'shelf'     => 'Skladištenje',
        'cabinet'   => 'Skladištenje',
        'chair'     => 'Stolice',
        'stool'     => 'Stolice',
        'bench'     => 'Stolice',
        'lamp'      => 'Rasveta',
        'light'     => 'Rasveta',
    ];

    /** Cena bazna po kategoriji (RSD). */
    private const PRICE_RANGES = [
        'Stolovi'      => [28000, 95000],
        'Stolice'      => [9000,  32000],
        'Skladištenje' => [22000, 110000],
        'Rasveta'      => [12000, 38000],
        'Dodaci'       => [4500,  18000],
    ];

    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        $delay = (float) $this->option('delay');
        $skipImages = (bool) $this->option('no-images');

        $this->info('Skrejpovanje opendesk.cc kataloga...');

        $listingHtml = $this->fetch(self::BASE_URL . '/designs');
        if ($listingHtml === null) {
            $this->error('Listing stranica nije dostupna.');
            return self::FAILURE;
        }

        $slugs = $this->extractSlugs($listingHtml);
        $this->info("Pronađeno {$slugs->count()} dizajna. Limit: {$limit}.");

        $designs = collect();
        foreach ($slugs->take($limit) as $i => $slug) {
            $this->line("[{$i}] {$slug}");
            $detailHtml = $this->fetch(self::BASE_URL . $slug);
            if ($detailHtml === null) {
                $this->warn('  preskačem (404 ili greška).');
                continue;
            }

            $design = $this->parseDetail($slug, $detailHtml);
            if ($design === null) {
                $this->warn('  preskačem (parsing greška).');
                continue;
            }

            if (!$skipImages && !empty($design['images_remote'])) {
                $design['images_local'] = $this->downloadImages($design['slug'], $design['images_remote']);
            } else {
                $design['images_local'] = [];
            }

            $designs->push($design);
            usleep((int) ($delay * 1_000_000));
        }

        $outputPath = base_path('../.data/opendesk.json');
        File::ensureDirectoryExists(dirname($outputPath));
        File::put($outputPath, json_encode($designs->values(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->info("Snimljeno {$designs->count()} dizajna u {$outputPath}");
        return self::SUCCESS;
    }

    private function fetch(string $url): ?string
    {
        try {
            $resp = Http::withHeaders([
                'User-Agent' => self::USER_AGENT,
                'Accept'     => 'text/html,application/xhtml+xml',
                'Accept-Language' => 'en',
            ])->timeout(30)->get($url);

            if (!$resp->successful()) {
                return null;
            }
            return $resp->body();
        } catch (\Throwable $e) {
            $this->warn("  fetch fail: {$e->getMessage()}");
            return null;
        }
    }

    /** Iz listing HTML-a vadi /parent/child slug-ove (samo dizajn detalji, ne about/contact). */
    private function extractSlugs(string $html): \Illuminate\Support\Collection
    {
        preg_match_all('#href="(/[a-z][a-z0-9-]+/[a-z0-9-]+)"#', $html, $matches);
        $excluded = ['/blog', '/news', '/about', '/contact', '/jobs', '/our-terms', '/workspaces'];

        return collect($matches[1] ?? [])
            ->unique()
            ->reject(function ($slug) use ($excluded) {
                foreach ($excluded as $prefix) {
                    if (str_starts_with($slug, $prefix)) return true;
                }
                return false;
            })
            ->values();
    }

    private function parseDetail(string $slug, string $html): ?array
    {
        // Naziv iz <h1>
        if (!preg_match('#<h1[^>]*>([^<]+)</h1>#', $html, $h1)) {
            return null;
        }
        $name = trim(html_entity_decode($h1[1], ENT_QUOTES, 'UTF-8'));

        // Slike — sve unique cloudinary URL-ove iz HTML-a
        preg_match_all(self::CLOUDINARY_PATTERN, $html, $imgMatches);
        $images = collect($imgMatches[0] ?? [])->unique()->values()->take(8)->all();
        if (empty($images)) {
            return null;
        }

        // Description — prvi <p> sa min 50 karaktera koji nije generički Opendesk boilerplate.
        // Boilerplate je "X Desk is designed to be manufactured by..." + svi paragrafi o supply chain-u.
        $boilerplateNeedles = [
            'alternative supply chain',
            'Open Making',
            'global community of makers',
            'designed to be manufactured by a network',
            'Get in touch',
            'Use the form',
            'cookies',
            'GDPR',
            'dedicated design service',
        ];
        preg_match_all('#<p[^>]*>([^<]+)</p>#', $html, $pMatches);
        $description = collect($pMatches[1] ?? [])
            ->map(fn ($p) => trim(html_entity_decode($p, ENT_QUOTES, 'UTF-8')))
            ->first(function ($p) use ($boilerplateNeedles, $name) {
                if (mb_strlen($p) < 50) return false;
                if (str_starts_with($p, 'Opendesk')) return false;
                if (str_starts_with($p, 'The ' . $name)) return false;
                foreach ($boilerplateNeedles as $needle) {
                    if (stripos($p, $needle) !== false) return false;
                }
                return true;
            });

        // Designer — pokušaj iz "Designed by X for Y" ili "by 57st." pattern-a
        $designer = null;
        if (preg_match('#Designed by ([^<\.,]+?)(?:\s+for|\s+\(|\.|<)#', $html, $dm)) {
            $designer = trim(html_entity_decode($dm[1], ENT_QUOTES, 'UTF-8'));
        } else {
            // Fallback: parent slug — npr. "studio-dlux" → "Studio Dlux"
            $parts = explode('/', trim($slug, '/'));
            if (count($parts) >= 2) {
                $designer = Str::title(str_replace('-', ' ', $parts[0]));
            }
        }

        // Kategorija po heuristici
        $category = $this->matchCategory($name);

        // Cena deterministička po slug-u (da reset nije svaki put drugačiji)
        $price = $this->generatePrice($slug, $category);

        // Lokalni slug za našu bazu (čistimo dvoslojni format na single)
        $localSlug = Str::slug($name);

        // Year — pokušaj iz description ili default 2024
        $year = null;
        if ($description && preg_match('#\b(20\d{2}|19\d{2})\b#', $description, $ym)) {
            $year = (int) $ym[1];
        }

        return [
            'opendesk_slug' => $slug,
            'slug'          => $localSlug,
            'name'          => $name,
            'designer'      => $designer,
            'year'          => $year,
            'category'      => $category,
            'description'   => $description ?: $name . ' — open-design nameštaj iz Opendesk kataloga.',
            'short_description' => $description ? Str::limit($description, 160) : null,
            'price'         => $price,
            'images_remote' => $images,
        ];
    }

    private function matchCategory(string $name): string
    {
        $lower = mb_strtolower($name);
        foreach (self::CATEGORY_RULES as $needle => $cat) {
            if (str_contains($lower, $needle)) return $cat;
        }
        return 'Dodaci';
    }

    private function generatePrice(string $slug, string $category): int
    {
        $range = self::PRICE_RANGES[$category] ?? [10000, 30000];
        $hash = abs(crc32($slug));
        $delta = $range[1] - $range[0];
        $raw = $range[0] + ($hash % $delta);
        // Zaokruzi na 500 RSD
        return (int) (round($raw / 500) * 500);
    }

    /** Skida slike u storage/app/public/products/opendesk/<slug>/. */
    private function downloadImages(string $localSlug, array $remoteUrls): array
    {
        $relativeDir = "products/opendesk/{$localSlug}";
        $absoluteDir = storage_path("app/public/{$relativeDir}");
        File::ensureDirectoryExists($absoluteDir);

        $local = [];
        foreach ($remoteUrls as $i => $url) {
            $ext = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
            $filename = sprintf('img-%02d.%s', $i + 1, $ext);
            $path = "{$absoluteDir}/{$filename}";

            if (file_exists($path)) {
                $local[] = "{$relativeDir}/{$filename}";
                continue;
            }

            try {
                $resp = Http::withHeaders(['User-Agent' => self::USER_AGENT])
                    ->timeout(60)
                    ->get($url);
                if ($resp->successful()) {
                    file_put_contents($path, $resp->body());
                    $local[] = "{$relativeDir}/{$filename}";
                }
            } catch (\Throwable $e) {
                $this->warn("    img fail: {$url}");
            }
        }

        return $local;
    }
}
