<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Voće i povrće' => ['Sveže voće', 'Sveže povrće', 'Sušeno voće'],
            'Mlečni proizvodi' => ['Sirevi', 'Jogurt', 'Mleko'],
            'Meso i riba' => ['Sveže meso', 'Suhomesnato', 'Riba'],
            'Pekara' => ['Hleb', 'Peciva', 'Kolači'],
            'Pića' => ['Sokovi', 'Voda', 'Čajevi'],
            'Domaćinstvo' => ['Sredstva za čišćenje', 'Higijena'],
        ];

        $sortOrder = 0;

        foreach ($categories as $parentName => $children) {
            $parent = Category::create([
                'name' => $parentName,
                'slug' => Str::slug($parentName),
                'description' => "Kategorija: {$parentName}",
                'sort_order' => $sortOrder++,
                'is_active' => true,
            ]);

            $childSort = 0;
            foreach ($children as $childName) {
                Category::create([
                    'parent_id' => $parent->id,
                    'name' => $childName,
                    'slug' => Str::slug($childName),
                    'description' => "{$childName} iz kategorije {$parentName}",
                    'sort_order' => $childSort++,
                    'is_active' => true,
                ]);
            }
        }
    }
}
