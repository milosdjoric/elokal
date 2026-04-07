<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ImportController extends Controller
{
    public function products(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $handle = fopen($request->file('file')->getPathname(), 'r');
        $header = fgetcsv($handle);

        $map = array_flip($header);
        $requiredCols = ['name', 'price'];
        foreach ($requiredCols as $col) {
            if (! isset($map[$col])) {
                fclose($handle);
                return response()->json(['message' => "Nedostaje kolona: {$col}"], 422);
            }
        }

        $created = 0;
        $updated = 0;
        $errors = [];
        $row = 1;

        while (($data = fgetcsv($handle)) !== false) {
            $row++;
            try {
                $name = $data[$map['name']] ?? null;
                if (! $name) {
                    $errors[] = "Red {$row}: ime je obavezno.";
                    continue;
                }

                $sku = $data[$map['sku'] ?? -1] ?? null;
                $slug = $data[$map['slug'] ?? -1] ?? Str::slug($name);

                $existing = $sku
                    ? Product::where('sku', $sku)->first()
                    : Product::where('slug', $slug)->first();

                $productData = [
                    'name' => $name,
                    'slug' => $slug,
                    'sku' => $sku,
                    'price' => (float) ($data[$map['price']] ?? 0),
                    'sale_price' => isset($map['sale_price']) ? ($data[$map['sale_price']] ?: null) : null,
                    'stock_quantity' => (int) ($data[$map['stock_quantity'] ?? -1] ?? 0),
                    'short_description' => $data[$map['short_description'] ?? -1] ?? null,
                    'description' => $data[$map['description'] ?? -1] ?? null,
                    'is_active' => in_array(strtolower($data[$map['is_active'] ?? -1] ?? 'yes'), ['yes', '1', 'true', 'da']),
                ];

                if ($existing) {
                    $existing->update($productData);
                    $updated++;
                } else {
                    // Unique slug
                    $baseSlug = $productData['slug'];
                    $counter = 1;
                    while (Product::where('slug', $productData['slug'])->exists()) {
                        $productData['slug'] = "{$baseSlug}-{$counter}";
                        $counter++;
                    }
                    Product::create($productData);
                    $created++;
                }
            } catch (\Exception $e) {
                $errors[] = "Red {$row}: {$e->getMessage()}";
            }
        }
        fclose($handle);

        return response()->json([
            'created' => $created,
            'updated' => $updated,
            'errors' => $errors,
        ]);
    }
}
