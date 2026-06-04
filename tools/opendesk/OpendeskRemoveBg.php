<?php

namespace App\Console\Commands;

use App\Models\ProductImage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

/**
 * Background removal za Opendesk slike preko `rembg` CLI (Python tool).
 *
 * Strategija:
 *   1) Iterira kroz sve podfoldere u storage/app/public/products/opendesk/
 *   2) Za svaki podfolder, pokrene `rembg p src_dir dst_dir` — batch obrada,
 *      model se učitava jednom po folderu (mnogo brže od per-file).
 *   3) Output: PNG sa transparent bg-om u istom folderu (img-NN.png pored img-NN.jpg).
 *   4) Update ProductImage records — image_path .jpg → .png.
 *
 * Zahtev: rembg instaliran globalno (pipx install "rembg[cli,cpu]").
 * Model u2net će se preuzeti pri prvom pokretanju (~176MB u ~/.u2net/).
 */
class OpendeskRemoveBg extends Command
{
    protected $signature = 'opendesk:remove-bg
                            {--force : Pokreni rembg i ako PNG već postoji (default: skip)}
                            {--db-only : Samo update DB image_path-eve, ne pokreći rembg}';

    protected $description = 'Skine pozadinu sa Opendesk slika preko rembg, snima kao PNG, update-uje ProductImage path-ove.';

    public function handle(): int
    {
        $rembgPath = trim((string) shell_exec('which rembg'));
        if (!$rembgPath && !$this->option('db-only')) {
            $this->error('rembg nije pronađen na PATH-u. Instaliraj: pipx install "rembg[cli,cpu]"');
            return self::FAILURE;
        }

        $base = storage_path('app/public');
        $opendeskRoot = "{$base}/products/opendesk";

        if (!is_dir($opendeskRoot)) {
            $this->error("Folder ne postoji: {$opendeskRoot}");
            return self::FAILURE;
        }

        // 1) Batch obrada slika ako nije --db-only
        if (!$this->option('db-only')) {
            $folders = collect(File::directories($opendeskRoot));
            $this->info("Pronađeno {$folders->count()} foldera za obradu.");

            $totalProcessed = 0;
            foreach ($folders as $i => $folder) {
                $folderName = basename($folder);
                $this->line(sprintf('[%d/%d] %s', $i + 1, $folders->count(), $folderName));

                $jpgs = collect(File::files($folder))
                    ->filter(fn ($f) => preg_match('/\.(jpg|jpeg)$/i', $f->getFilename()));

                $needsProcessing = $jpgs->filter(function ($f) use ($folder) {
                    $pngPath = "{$folder}/" . pathinfo($f->getFilename(), PATHINFO_FILENAME) . '.png';
                    return $this->option('force') || !file_exists($pngPath);
                });

                if ($needsProcessing->isEmpty()) {
                    $this->line('   već obrađeno, preskačem.');
                    continue;
                }

                // rembg p source dest — batch obrada folder-a
                $process = new Process([$rembgPath, 'p', $folder, $folder]);
                $process->setTimeout(600);
                $process->run();

                if (!$process->isSuccessful()) {
                    $this->warn("   greška: " . $process->getErrorOutput());
                    continue;
                }

                $totalProcessed += $needsProcessing->count();
                $this->line("   obrađeno {$needsProcessing->count()} slika.");
            }

            $this->info("\nObrađeno ukupno: {$totalProcessed} slika.");
        }

        // 2) Update ProductImage records — .jpg → .png
        $this->info("\nAžuriranje ProductImage path-ova...");

        $images = ProductImage::where('image_path', 'like', 'products/opendesk/%')
            ->where('image_path', 'not like', '%.png')
            ->get();

        $updated = 0;
        foreach ($images as $img) {
            $newPath = preg_replace('/\.(jpg|jpeg)$/i', '.png', $img->image_path);
            $absNew = "{$base}/{$newPath}";

            if (file_exists($absNew)) {
                $img->image_path = $newPath;
                $img->save();
                $updated++;
            }
        }

        $this->info("Ažurirano {$updated} ProductImage zapisa.");
        return self::SUCCESS;
    }
}
