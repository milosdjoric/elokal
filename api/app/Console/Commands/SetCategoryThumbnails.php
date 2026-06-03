<?php

namespace App\Console\Commands;

use App\Models\Category;
use Illuminate\Console\Command;

/**
 * Postavlja `image_path` svakoj kategoriji na primary sliku prvog proizvoda u toj kategoriji.
 * Pokrenuti posle ProductSeeder-a / Opendesk import-a.
 */
class SetCategoryThumbnails extends Command
{
    protected $signature = 'categories:set-thumbnails {--force : Prepiši i ako već postoji image_path}';

    protected $description = 'Za svaku kategoriju postavi thumbnail kao primary sliku prvog povezanog proizvoda.';

    public function handle(): int
    {
        $categories = Category::with(['products' => function ($q) {
            $q->where('is_active', true)->orderBy('sort_order');
        }, 'products.images'])->get();

        $updated = 0;
        foreach ($categories as $cat) {
            if ($cat->image_path && !$this->option('force')) {
                $this->line("· {$cat->name} — već ima thumbnail, preskačem.");
                continue;
            }

            $product = $cat->products->first();
            if (!$product) {
                $this->warn("· {$cat->name} — nema proizvoda.");
                continue;
            }

            $primary = $product->images->firstWhere('is_primary', true) ?? $product->images->first();
            if (!$primary) {
                $this->warn("· {$cat->name} → {$product->name} — proizvod nema slike.");
                continue;
            }

            $cat->image_path = $primary->image_path;
            $cat->save();
            $updated++;
            $this->info("✓ {$cat->name} → {$primary->image_path}");
        }

        $this->info("\nUkupno ažurirano: {$updated} kategorija.");
        return self::SUCCESS;
    }
}
