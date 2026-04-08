<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ImportProductImages;
use App\Models\ImportLog;
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
        $imageImportQueue = [];

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
                    $targetProduct = $existing;
                    $updated++;
                } else {
                    // Unique slug
                    $baseSlug = $productData['slug'];
                    $counter = 1;
                    while (Product::where('slug', $productData['slug'])->exists()) {
                        $productData['slug'] = "{$baseSlug}-{$counter}";
                        $counter++;
                    }
                    $targetProduct = Product::create($productData);
                    $created++;
                }

                // Slike iz URL-ova (comma-separated)
                if (isset($map['image_urls'])) {
                    $imageUrlsRaw = $data[$map['image_urls']] ?? '';
                    if ($imageUrlsRaw) {
                        $imageUrls = array_filter(array_map('trim', explode(',', $imageUrlsRaw)));
                        if (count($imageUrls) > 0) {
                            $imageImportQueue[] = [
                                'product_id' => $targetProduct->id,
                                'urls' => $imageUrls,
                            ];
                        }
                    }
                }
            } catch (\Exception $e) {
                $errors[] = "Red {$row}: {$e->getMessage()}";
            }
        }
        fclose($handle);

        $importLog = ImportLog::create([
            'admin_id' => $request->user()->id,
            'type' => 'products',
            'filename' => $request->file('file')->getClientOriginalName(),
            'rows_total' => $row - 1,
            'rows_created' => $created,
            'rows_updated' => $updated,
            'rows_failed' => count($errors),
            'errors' => $errors ?: null,
            'status' => count($errors) === 0 ? 'completed' : ($created + $updated > 0 ? 'partial' : 'failed'),
        ]);

        // Dispatch image import jobs
        foreach ($imageImportQueue as $imageJob) {
            ImportProductImages::dispatch(
                $imageJob['product_id'],
                $imageJob['urls'],
                $importLog->id,
            );
        }

        return response()->json([
            'created' => $created,
            'updated' => $updated,
            'errors' => $errors,
        ]);
    }

    public function history(): JsonResponse
    {
        $logs = ImportLog::with('admin')
            ->orderByDesc('created_at')
            ->paginate(15);

        return response()->json($logs);
    }
}
