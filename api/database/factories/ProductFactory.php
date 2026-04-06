<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(rand(2, 4), true);
        $price = fake()->randomFloat(2, 100, 50000);

        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'short_description' => fake()->sentence(),
            'description' => fake()->paragraphs(3, true),
            'price' => $price,
            'sale_price' => null,
            'sale_price_from' => null,
            'sale_price_to' => null,
            'cost_price' => round($price * 0.6, 2),
            'unit_price' => null,
            'unit_label' => null,
            'sku' => strtoupper(fake()->unique()->bothify('??-####')),
            'stock_quantity' => fake()->numberBetween(0, 200),
            'is_active' => true,
            'featured' => fake()->boolean(20),
            'sort_order' => 0,
            'meta_title' => null,
            'meta_description' => null,
        ];
    }

    public function onSale(): static
    {
        return $this->state(function (array $attributes) {
            $salePrice = round($attributes['price'] * fake()->randomFloat(2, 0.5, 0.9), 2);

            return [
                'sale_price' => $salePrice,
                'sale_price_from' => now()->subDays(5),
                'sale_price_to' => now()->addDays(30),
            ];
        });
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
