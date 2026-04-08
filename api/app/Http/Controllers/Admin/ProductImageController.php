<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductImageRequest;
use App\Jobs\ProcessProductImage;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductImageController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ProductImage::with('product:id,name,slug')
            ->orderByDesc('created_at');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('alt_text', 'like', "%{$search}%")
                  ->orWhereHas('product', fn ($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('folder_id')) {
            $query->where('folder_id', $request->folder_id);
        } elseif ($request->has('folder_id') && $request->folder_id === null) {
            $query->whereNull('folder_id');
        }

        $perPage = min($request->input('per_page', 24), 48);

        return response()->json($query->paginate($perPage));
    }

    public function update(Request $request, ProductImage $image): JsonResponse
    {
        $request->validate([
            'alt_text' => 'nullable|string|max:255',
        ]);

        $image->update(['alt_text' => $request->alt_text]);

        return response()->json($image);
    }

    public function store(ProductImageRequest $request, Product $product): JsonResponse
    {
        $path = $request->file('image')->store("products/{$product->id}", 'public');

        $sortOrder = $product->images()->max('sort_order') + 1;

        // Ako je primary, resetuj ostale
        if ($request->boolean('is_primary')) {
            $product->images()->update(['is_primary' => false]);
        }

        $isPrimary = $request->boolean('is_primary') || $product->images()->count() === 0;

        $image = $product->images()->create([
            'image_path' => $path,
            'alt_text' => $request->alt_text,
            'sort_order' => $sortOrder,
            'is_primary' => $isPrimary,
        ]);

        ProcessProductImage::dispatch($image);

        return response()->json($image, 201);
    }

    public function destroy(Product $product, ProductImage $image): JsonResponse
    {
        if ($image->product_id !== $product->id) {
            abort(404);
        }

        // Obrisi sve varijante sa diska
        $disk = Storage::disk('public');
        $pathInfo = pathinfo($image->image_path);
        $dir = $pathInfo['dirname'];
        $baseName = $pathInfo['filename'];

        $disk->delete($image->image_path);
        foreach (['thumbnail', 'medium', 'large'] as $size) {
            $disk->delete("{$dir}/{$baseName}_{$size}.webp");
        }
        $disk->delete("{$dir}/{$baseName}.webp");

        $wasPrimary = $image->is_primary;
        $image->delete();

        // Ako je obrisana primary, postavi prvu preostalu
        if ($wasPrimary) {
            $product->images()->orderBy('sort_order')->first()?->update(['is_primary' => true]);
        }

        return response()->json(['message' => 'Slika obrisana.']);
    }

    public function reorder(Request $request, Product $product): JsonResponse
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'exists:product_images,id',
        ]);

        foreach ($request->order as $index => $imageId) {
            $product->images()->where('id', $imageId)->update(['sort_order' => $index]);
        }

        return response()->json(['message' => 'Redosled ažuriran.']);
    }
}
