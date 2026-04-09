<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;

class AttributeVariantSeeder extends Seeder
{
    public function run(): void
    {
        // Atributi
        $color = Attribute::firstOrCreate(['slug' => 'boja'], [
            'name' => 'Boja',
            'type' => 'color',
            'is_filterable' => true,
            'is_visible_on_card' => true,
            'sort_order' => 0,
        ]);

        $size = Attribute::firstOrCreate(['slug' => 'velicina'], [
            'name' => 'Veličina',
            'type' => 'select',
            'is_filterable' => true,
            'is_visible_on_card' => true,
            'sort_order' => 1,
        ]);

        // Boje
        $colors = [
            ['value' => 'Bela', 'color_hex' => '#FFFFFF', 'sort_order' => 0],
            ['value' => 'Bež', 'color_hex' => '#F5F5DC', 'sort_order' => 1],
            ['value' => 'Roze', 'color_hex' => '#FFB6C1', 'sort_order' => 2],
            ['value' => 'Plava', 'color_hex' => '#87CEEB', 'sort_order' => 3],
            ['value' => 'Zelena', 'color_hex' => '#98D8A8', 'sort_order' => 4],
            ['value' => 'Siva', 'color_hex' => '#C0C0C0', 'sort_order' => 5],
            ['value' => 'Smeđa', 'color_hex' => '#8B4513', 'sort_order' => 6],
        ];

        $colorValues = [];
        foreach ($colors as $c) {
            $colorValues[$c['value']] = AttributeValue::firstOrCreate(
                ['attribute_id' => $color->id, 'value' => $c['value']],
                $c,
            );
        }

        // Veličine
        $sizes = [
            ['value' => 'S', 'sort_order' => 0],
            ['value' => 'M', 'sort_order' => 1],
            ['value' => 'L', 'sort_order' => 2],
            ['value' => 'XL', 'sort_order' => 3],
        ];

        $sizeValues = [];
        foreach ($sizes as $s) {
            $sizeValues[$s['value']] = AttributeValue::firstOrCreate(
                ['attribute_id' => $size->id, 'value' => $s['value']],
                $s,
            );
        }

        // Varijante za tepih — boje
        $tepihOblak = Product::find(52); // Dečiji tepih oblak
        if ($tepihOblak && $tepihOblak->variants()->count() === 0) {
            $basePrice = (float) $tepihOblak->price;

            foreach (['Bela', 'Bež', 'Roze', 'Siva'] as $i => $colorName) {
                $variant = ProductVariant::create([
                    'product_id' => $tepihOblak->id,
                    'sku' => $tepihOblak->sku . '-' . strtoupper(substr($colorName, 0, 3)),
                    'price' => $basePrice + ($i * 200),
                    'stock_quantity' => rand(5, 30),
                    'is_active' => true,
                ]);
                $variant->attributeValues()->attach($colorValues[$colorName]->id);
            }
        }

        // Varijante za fotelju — boje + veličine
        $fotelja = Product::find(55); // Dečija fotelja
        if ($fotelja && $fotelja->variants()->count() === 0) {
            $basePrice = (float) $fotelja->price;

            foreach (['Smeđa', 'Bež', 'Siva'] as $colorName) {
                foreach (['S', 'M', 'L'] as $sizeName) {
                    $priceAdj = ($sizeName === 'M' ? 500 : ($sizeName === 'L' ? 1200 : 0));
                    $variant = ProductVariant::create([
                        'product_id' => $fotelja->id,
                        'sku' => $fotelja->sku . '-' . strtoupper(substr($colorName, 0, 3)) . '-' . $sizeName,
                        'price' => $basePrice + $priceAdj,
                        'stock_quantity' => rand(3, 15),
                        'is_active' => true,
                    ]);
                    $variant->attributeValues()->attach([
                        $colorValues[$colorName]->id,
                        $sizeValues[$sizeName]->id,
                    ]);
                }
            }
        }

        // Varijante za lampe — boje
        $lampaIds = [6, 23, 24]; // Stone lampe
        foreach ($lampaIds as $lampaId) {
            $lampa = Product::find($lampaId);
            if (! $lampa || $lampa->variants()->count() > 0) continue;

            $basePrice = (float) $lampa->price;
            $lampColors = ['Bela', 'Roze', 'Plava'];

            foreach ($lampColors as $i => $colorName) {
                $variant = ProductVariant::create([
                    'product_id' => $lampa->id,
                    'sku' => $lampa->sku . '-' . strtoupper(substr($colorName, 0, 3)),
                    'price' => $basePrice,
                    'stock_quantity' => rand(5, 20),
                    'is_active' => true,
                ]);
                $variant->attributeValues()->attach($colorValues[$colorName]->id);
            }
        }

        // Varijante za tepih srna — veličine
        $tepihSrna = Product::find(51);
        if ($tepihSrna && $tepihSrna->variants()->count() === 0) {
            $basePrice = (float) $tepihSrna->price;

            $tepihSizes = [
                ['size' => 'S', 'label' => '60x80', 'adj' => -800],
                ['size' => 'M', 'label' => '90x100', 'adj' => 0],
                ['size' => 'L', 'label' => '120x160', 'adj' => 2500],
                ['size' => 'XL', 'label' => '160x200', 'adj' => 5000],
            ];

            foreach ($tepihSizes as $ts) {
                $variant = ProductVariant::create([
                    'product_id' => $tepihSrna->id,
                    'sku' => $tepihSrna->sku . '-' . $ts['size'],
                    'price' => max(0, $basePrice + $ts['adj']),
                    'stock_quantity' => rand(5, 20),
                    'is_active' => true,
                ]);
                $variant->attributeValues()->attach($sizeValues[$ts['size']]->id);
            }
        }
    }
}
