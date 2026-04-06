<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $childCategories = Category::whereNotNull('parent_id')->get();

        // 30 proizvoda: 24 obicnih + 6 na akciji
        $products = Product::factory(24)->create();
        $saleProducts = Product::factory(6)->onSale()->create();

        $allProducts = $products->merge($saleProducts);

        foreach ($allProducts as $product) {
            // Svaki proizvod ima 1-3 slike, prva je primary
            ProductImage::factory()
                ->primary()
                ->create(['product_id' => $product->id, 'sort_order' => 0]);

            $extraImages = rand(0, 2);
            for ($i = 1; $i <= $extraImages; $i++) {
                ProductImage::factory()
                    ->create(['product_id' => $product->id, 'sort_order' => $i]);
            }

            // Dodeli 1-2 kategorije
            $randomCategories = $childCategories->random(rand(1, 2));
            $product->categories()->attach($randomCategories->pluck('id'));
        }
    }
}
