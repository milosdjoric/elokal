<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductPriceHistory;
use Illuminate\Console\Command;

class BackfillPriceHistory extends Command
{
    protected $signature = 'price-history:backfill';

    protected $description = 'Snima trenutnu cenu svakog proizvoda u price history (jednokratno, posle deploy-a Observer-a).';

    public function handle(): int
    {
        $count = 0;

        Product::query()->chunk(100, function ($products) use (&$count) {
            foreach ($products as $product) {
                $exists = ProductPriceHistory::where('product_id', $product->id)->exists();
                if ($exists) {
                    continue;
                }

                $effective = $product->isSaleActive() && $product->sale_price
                    ? (float) $product->sale_price
                    : (float) $product->price;

                ProductPriceHistory::create([
                    'product_id' => $product->id,
                    'price' => $product->price,
                    'sale_price' => $product->sale_price,
                    'effective_price' => $effective,
                    'recorded_at' => $product->updated_at ?? now(),
                ]);

                $count++;
            }
        });

        $this->info("Backfilled {$count} products.");

        return self::SUCCESS;
    }
}
