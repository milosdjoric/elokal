<?php

namespace App\Jobs;

use App\Models\ImportLog;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportProductImages implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public int $timeout = 120;

    public function __construct(
        public int $productId,
        public array $imageUrls,
        public int $importLogId,
    ) {}

    public function handle(): void
    {
        $product = Product::find($this->productId);
        if (! $product) {
            return;
        }

        $disk = Storage::disk('public');
        $dir = "products/{$product->id}";
        $disk->makeDirectory($dir);

        $existingCount = $product->images()->count();
        $failed = 0;

        foreach ($this->imageUrls as $index => $url) {
            $url = trim($url);
            try {
                if (! filter_var($url, FILTER_VALIDATE_URL)) {
                    $failed++;
                    continue;
                }

                $response = Http::timeout(15)->get($url);
                if (! $response->successful()) {
                    $failed++;
                    continue;
                }

                $contentType = $response->header('Content-Type');
                $extension = match (true) {
                    str_contains($contentType, 'png') => 'png',
                    str_contains($contentType, 'webp') => 'webp',
                    str_contains($contentType, 'gif') => 'gif',
                    default => 'jpg',
                };

                $filename = Str::uuid().'.'.$extension;
                $path = "{$dir}/{$filename}";

                $disk->put($path, $response->body());

                $image = ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $path,
                    'alt_text' => $product->name,
                    'sort_order' => $existingCount + $index,
                    'is_primary' => ($existingCount === 0 && $index === 0),
                ]);

                ProcessProductImage::dispatch($image);
            } catch (\Exception $e) {
                Log::warning("Image import failed for URL: {$url} — {$e->getMessage()}");
                $failed++;
            }
        }

        // Dodaj greške u ImportLog ako ima neuspelih
        if ($failed > 0) {
            $log = ImportLog::find($this->importLogId);
            if ($log) {
                $errors = $log->errors ?? [];
                $errors[] = "Proizvod #{$this->productId}: {$failed}/{$this->imageUrlCount()} slika nije preuzeto.";
                $log->update(['errors' => $errors]);
            }
        }
    }

    private function imageUrlCount(): int
    {
        return count($this->imageUrls);
    }
}
