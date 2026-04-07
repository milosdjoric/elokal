<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $json = json_decode(file_get_contents(base_path('../.data/demo_deciji_namestaj.json')), true);

        // Napravimo mapu leaf kategorija po imenu
        $leafCategories = Category::whereDoesntHave('children')->get()->keyBy('name');

        foreach ($json as $index => $item) {
            $slug = Str::slug($item['name']);

            // Osiguraj unique slug
            if (Product::where('slug', $slug)->exists()) {
                $slug .= '-' . Str::random(4);
            }

            $product = Product::create([
                'name' => $item['name'],
                'slug' => $slug,
                'short_description' => $item['short_description'] ?? null,
                'description' => $item['description'] ?? null,
                'price' => $item['price'] * 117.5, // EUR to RSD approx
                'sale_price' => $item['special_price'] ? $item['special_price'] * 117.5 : null,
                'cost_price' => round($item['price'] * 117.5 * 0.6, 2),
                'sku' => $item['sku'],
                'stock_quantity' => rand(5, 50),
                'is_active' => true,
                'featured' => $index < 8,
                'sort_order' => $index,
                'meta_title' => $item['meta_title'] ?? null,
                'meta_description' => $item['meta_description'] ?? null,
            ]);

            // Slike — koristimo eksterne URL-ove kao image_path
            if (!empty($item['image'])) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $item['image'],
                    'alt_text' => $item['name'],
                    'sort_order' => 0,
                    'is_primary' => true,
                ]);
            }

            if (!empty($item['hover_image'])) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $item['hover_image'],
                    'alt_text' => $item['name'] . ' - hover',
                    'sort_order' => 1,
                    'is_primary' => false,
                ]);
            }

            // Kategorija — poslednji segment iz path-a
            if (!empty($item['categories'])) {
                $parts = array_map('trim', explode('>', $item['categories']));
                $leafName = end($parts);

                if ($leafCategories->has($leafName)) {
                    $product->categories()->attach($leafCategories->get($leafName)->id);
                }
            }
        }
    }
}
