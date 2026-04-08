<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductResource;
use App\Models\Attribute;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
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

        // Filtriranje po atributima: attributes[color]=1,3&attributes[size]=5
        if ($request->has('attributes')) {
            $attrFilters = $request->input('attributes', []);
            foreach ($attrFilters as $slug => $valueIds) {
                $ids = is_string($valueIds) ? explode(',', $valueIds) : (array) $valueIds;
                $ids = array_filter(array_map('intval', $ids));
                if (! empty($ids)) {
                    $query->whereHas('variants.attributeValues', function ($q) use ($ids, $slug) {
                        $q->whereIn('attribute_values.id', $ids)
                          ->whereHas('attribute', fn ($aq) => $aq->where('slug', $slug));
                    });
                }
            }
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

    public function filters(): JsonResponse
    {
        // Price range
        $minPrice = Product::where('is_active', true)->min('price') ?? 0;
        $maxPrice = Product::where('is_active', true)->max('price') ?? 0;

        // Filterable atributi sa vrednostima
        $attributes = Attribute::where('is_filterable', true)
            ->with(['values' => fn ($q) => $q->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($attr) => [
                'id' => $attr->id,
                'name' => $attr->name,
                'slug' => $attr->slug,
                'type' => $attr->type,
                'values' => $attr->values->map(fn ($v) => [
                    'id' => $v->id,
                    'value' => $v->value,
                    'color_hex' => $v->color_hex,
                    'image_path' => $v->image_path,
                ]),
            ]);

        return response()->json([
            'price_range' => ['min' => (float) $minPrice, 'max' => (float) $maxPrice],
            'attributes' => $attributes,
        ]);
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

        // Prev/Next u istoj kategoriji
        $categoryId = $product->categories->first()?->id;
        $prevNext = ['prev' => null, 'next' => null];
        if ($categoryId) {
            $baseQuery = Product::where('is_active', true)
                ->whereHas('categories', fn ($q) => $q->where('categories.id', $categoryId));

            $prev = (clone $baseQuery)->where('id', '<', $product->id)->orderByDesc('id')->first(['id', 'slug', 'name']);
            $next = (clone $baseQuery)->where('id', '>', $product->id)->orderBy('id')->first(['id', 'slug', 'name']);

            if ($prev) $prevNext['prev'] = ['slug' => $prev->slug, 'name' => $prev->name];
            if ($next) $prevNext['next'] = ['slug' => $next->slug, 'name' => $next->name];
        }

        return (new ProductResource($product))->additional(['prev_next' => $prevNext]);
    }
}
