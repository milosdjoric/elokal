<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $json = json_decode(file_get_contents(base_path('../.data/demo_deciji_namestaj.json')), true);

        $categoryPaths = collect($json)->pluck('categories')->unique()->sort()->values();

        $created = [];

        foreach ($categoryPaths as $path) {
            $parts = array_map('trim', explode('>', $path));

            // Preskoci prvi nivo ("Dečiji nameštaj i dekoracija") — nije korisna kategorija
            if (count($parts) > 1) {
                array_shift($parts);
            }

            $parentId = null;

            foreach ($parts as $index => $name) {
                $key = implode(' > ', array_slice($parts, 0, $index + 1));

                if (isset($created[$key])) {
                    $parentId = $created[$key];
                    continue;
                }

                $slug = Str::slug($name);
                // Osiguraj unique slug
                if (Category::where('slug', $slug)->exists()) {
                    $slug .= '-' . Str::random(4);
                }

                $category = Category::create([
                    'parent_id' => $parentId,
                    'name' => $name,
                    'slug' => $slug,
                    'description' => $name,
                    'sort_order' => 0,
                    'is_active' => true,
                ]);

                $created[$key] = $category->id;
                $parentId = $category->id;
            }
        }
    }
}
