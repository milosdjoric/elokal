<?php

namespace App\Http\Controllers;

use App\Enums\FeatureFlag;
use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $baseUrl = config('app.frontend_url', 'http://localhost:3000');
        $available = Setting::getValue('languages_available', 'sr');
        $locales = array_map('trim', explode(',', $available));
        $defaultLocale = Setting::getValue('languages_default', 'sr');
        $hasMultiLang = count($locales) > 1 && feature(FeatureFlag::MultiLanguage);

        $urls = [];

        // Statičke stranice
        $urls[] = ['loc' => $baseUrl, 'priority' => '1.0', 'changefreq' => 'daily'];
        $urls[] = ['loc' => "{$baseUrl}/proizvodi", 'priority' => '0.9', 'changefreq' => 'daily'];
        $urls[] = ['loc' => "{$baseUrl}/blog", 'priority' => '0.7', 'changefreq' => 'weekly'];

        // Proizvodi
        Product::where('is_active', true)->select('slug', 'updated_at')->chunk(500, function ($products) use (&$urls, $baseUrl) {
            foreach ($products as $product) {
                $urls[] = [
                    'loc' => "{$baseUrl}/proizvodi/{$product->slug}",
                    'lastmod' => $product->updated_at->toAtomString(),
                    'priority' => '0.8',
                    'changefreq' => 'weekly',
                ];
            }
        });

        // Kategorije
        Category::where('is_active', true)->select('slug', 'updated_at')->each(function ($cat) use (&$urls, $baseUrl) {
            $urls[] = [
                'loc' => "{$baseUrl}/kategorije/{$cat->slug}",
                'lastmod' => $cat->updated_at->toAtomString(),
                'priority' => '0.7',
                'changefreq' => 'weekly',
            ];
        });

        // Blog postovi
        Post::published()->select('slug', 'updated_at')->each(function ($post) use (&$urls, $baseUrl) {
            $urls[] = [
                'loc' => "{$baseUrl}/blog/{$post->slug}",
                'lastmod' => $post->updated_at->toAtomString(),
                'priority' => '0.6',
                'changefreq' => 'monthly',
            ];
        });

        // Statičke stranice (CMS)
        Page::active()->select('slug', 'updated_at')->each(function ($page) use (&$urls, $baseUrl) {
            $urls[] = [
                'loc' => "{$baseUrl}/{$page->slug}",
                'lastmod' => $page->updated_at->toAtomString(),
                'priority' => '0.5',
                'changefreq' => 'monthly',
            ];
        });

        // XML generisanje
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"';
        if ($hasMultiLang) {
            $xml .= ' xmlns:xhtml="http://www.w3.org/1999/xhtml"';
        }
        $xml .= '>';

        foreach ($urls as $url) {
            $xml .= '<url>';
            $xml .= "<loc>{$url['loc']}</loc>";
            if (isset($url['lastmod'])) $xml .= "<lastmod>{$url['lastmod']}</lastmod>";
            $xml .= "<changefreq>{$url['changefreq']}</changefreq>";
            $xml .= "<priority>{$url['priority']}</priority>";

            // hreflang linkovi
            if ($hasMultiLang) {
                foreach ($locales as $loc) {
                    $hrefLoc = $url['loc'] . ($loc !== $defaultLocale ? "?locale={$loc}" : '');
                    $xml .= "<xhtml:link rel=\"alternate\" hreflang=\"{$loc}\" href=\"{$hrefLoc}\" />";
                }
            }

            $xml .= '</url>';
        }
        $xml .= '</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }

    public function robots(): Response
    {
        $baseUrl = config('app.url', 'http://localhost:8000');
        $content = "User-agent: *\nAllow: /\n\nSitemap: {$baseUrl}/sitemap.xml\n";

        return response($content, 200, ['Content-Type' => 'text/plain']);
    }
}
