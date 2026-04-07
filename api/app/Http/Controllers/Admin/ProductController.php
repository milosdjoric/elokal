<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductFormRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): ProductCollection
    {
        $query = Product::with(['categories', 'images']);

        // Filteri
        if ($request->has('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->has('category')) {
            $query->whereHas('categories', fn ($q) => $q->where('categories.id', $request->category));
        }

        if ($request->boolean('featured')) {
            $query->where('featured', true);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('short_description', 'like', "%{$search}%");
            });
        }

        // Sortiranje
        $sortField = $request->input('sort', 'created_at');
        $sortDir = $request->input('direction', 'desc');

        $allowedSorts = ['name', 'price', 'created_at', 'sort_order'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDir === 'asc' ? 'asc' : 'desc');
        }

        // Paginacija
        $perPage = min($request->input('per_page', 15), 100);

        return new ProductCollection($query->paginate($perPage));
    }

    public function store(ProductFormRequest $request): JsonResponse
    {
        $product = Product::create($request->validated());

        if ($request->has('categories')) {
            $product->categories()->sync($request->categories);
        }

        $product->load(['categories', 'images']);

        return (new ProductResource($product))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Product $product): ProductResource
    {
        $product->load(['categories', 'images']);

        return new ProductResource($product);
    }

    public function update(ProductUpdateRequest $request, Product $product): ProductResource
    {
        $product->update($request->validated());

        if ($request->has('categories')) {
            $product->categories()->sync($request->categories);
        }

        $product->load(['categories', 'images']);

        return new ProductResource($product);
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json(['message' => 'Proizvod obrisan.']);
    }

    public function relations(Product $product): JsonResponse
    {
        return response()->json([
            'related' => $product->relatedProducts()->with('images')->get()->pluck('id'),
            'cross_sell' => $product->crossSellProducts()->with('images')->get()->pluck('id'),
            'up_sell' => $product->upSellProducts()->with('images')->get()->pluck('id'),
        ]);
    }

    public function updateRelations(Request $request, Product $product): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:related,cross_sell,up_sell',
            'product_ids' => 'present|array',
            'product_ids.*' => 'exists:products,id',
        ]);

        // Obriši stare relacije za ovaj tip
        \Illuminate\Support\Facades\DB::table('product_relations')
            ->where('product_id', $product->id)
            ->where('type', $request->type)
            ->delete();

        // Kreiraj nove
        foreach ($request->product_ids as $index => $relatedId) {
            if ($relatedId == $product->id) continue;
            \Illuminate\Support\Facades\DB::table('product_relations')->insert([
                'product_id' => $product->id,
                'related_product_id' => $relatedId,
                'type' => $request->type,
                'sort_order' => $index,
            ]);
        }

        return response()->json(['message' => 'Relacije ažurirane.']);
    }
}
