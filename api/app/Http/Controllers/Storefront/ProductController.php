<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): ProductCollection
    {
        $query = Product::with(['categories', 'images'])
            ->where('is_active', true);

        if ($request->has('category')) {
            $categoryId = (int) $request->category;
            $allIds = collect([$categoryId]);

            // Rekurzivno sakupi sve podkategorije (svi nivoi)
            $parentIds = collect([$categoryId]);
            while ($parentIds->isNotEmpty()) {
                $childIds = Category::whereIn('parent_id', $parentIds)->pluck('id');
                $allIds = $allIds->merge($childIds);
                $parentIds = $childIds;
            }

            $query->whereHas('categories', fn ($q) => $q->whereIn('categories.id', $allIds));
        }

        if ($request->boolean('featured')) {
            $query->where('featured', true);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('short_description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        $sortField = $request->input('sort', 'created_at');
        $sortDir = $request->input('direction', 'desc');

        $allowedSorts = ['name', 'price', 'created_at'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDir === 'asc' ? 'asc' : 'desc');
        }

        $perPage = min($request->input('per_page', 12), 48);

        return new ProductCollection($query->paginate($perPage));
    }

    public function show(string $slug): ProductResource
    {
        $product = Product::with(['categories', 'images', 'variants.attributeValues.attribute', 'variants.images'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        // Učitaj ručne relacije, ili fallback na auto (ista kategorija)
        $product->load([
            'crossSellProducts' => fn ($q) => $q->where('is_active', true)->with('images'),
            'upSellProducts' => fn ($q) => $q->where('is_active', true)->with('images'),
            'relatedProducts' => fn ($q) => $q->where('is_active', true)->with('images'),
        ]);

        return new ProductResource($product);
    }
}
