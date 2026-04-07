<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductCollection;
use App\Models\Category;
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

    public function show(Request $request, string $slug): ProductCollection
    {
        $category = Category::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $products = $category->products()
            ->where('is_active', true)
            ->with('images')
            ->orderBy('sort_order')
            ->paginate(min($request->input('per_page', 12), 48));

        return (new ProductCollection($products))
            ->additional(['category' => new CategoryResource($category)]);
    }
}
