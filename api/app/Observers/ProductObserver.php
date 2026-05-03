<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\ProductPriceHistory;

class ProductObserver
{
    public function created(Product $product): void
    {
        $this->recordPrice($product);
    }

    public function updated(Product $product): void
    {
        if ($product->wasChanged(['price', 'sale_price', 'sale_price_from', 'sale_price_to'])) {
            $this->recordPrice($product);
        }
    }

    private function recordPrice(Product $product): void
    {
        $effective = $product->isSaleActive() && $product->sale_price
            ? (float) $product->sale_price
            : (float) $product->price;

        ProductPriceHistory::create([
            'product_id' => $product->id,
            'price' => $product->price,
            'sale_price' => $product->sale_price,
            'effective_price' => $effective,
            'recorded_at' => now(),
        ]);
    }
}
