<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductCollection;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::whereNull('parent_id')
            ->where('is_active', true)
            ->with(['children' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'data' => CategoryResource::collection($categories),
        ]);
    }

    /**
     * Prikaz jedne (aktivne) kategorije + paginirani aktivni proizvodi te kategorije.
     * 404 ako kategorija ne postoji ili nije aktivna.
     */
    public function show(Request $request, string $slug): JsonResponse
    {
        $category = Category::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $perPage = min((int) $request->input('per_page', 12), 48);

        $products = Product::with(['categories', 'images'])
            ->where('is_active', true)
            ->whereHas('categories', fn ($q) => $q->where('categories.id', $category->id))
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return (new ProductCollection($products))
            ->additional(['category' => new CategoryResource($category)])
            ->response();
    }
}
