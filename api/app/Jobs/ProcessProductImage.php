<?php

namespace App\Jobs;

use App\Models\ProductImage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ProcessProductImage implements ShouldQueue
{
    use Queueable;

    private const SIZES = [
        'thumbnail' => [150, 150],
        'medium' => [600, 600],
        'large' => [1200, 1200],
    ];

    public function __construct(
        public ProductImage $productImage,
    ) {}

    public function handle(): void
    {
        $disk = Storage::disk('public');
        $originalPath = $this->productImage->image_path;

        if (! $disk->exists($originalPath)) {
            return;
        }

        $manager = new ImageManager(new Driver());
        $image = $manager->read($disk->get($originalPath));

        $pathInfo = pathinfo($originalPath);
        $baseName = $pathInfo['filename'];
        $dir = $pathInfo['dirname'];

        foreach (self::SIZES as $sizeName => [$width, $height]) {
            $resized = clone $image;
            $resized->scaleDown($width, $height);

            $webpPath = "{$dir}/{$baseName}_{$sizeName}.webp";
            $disk->put($webpPath, $resized->toWebp(quality: 80)->toString());
        }

        // Originalu WebP verzija
        $webpOriginal = "{$dir}/{$baseName}.webp";
        if ($pathInfo['extension'] !== 'webp') {
            $disk->put($webpOriginal, $image->toWebp(quality: 90)->toString());
        }
    }
}
