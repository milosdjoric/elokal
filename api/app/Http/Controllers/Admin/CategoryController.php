<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryFormRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::whereNull('parent_id')
            ->with(['children' => fn ($q) => $q->orderBy('sort_order')->withCount('products')
                ->with(['children' => fn ($q2) => $q2->orderBy('sort_order')->withCount('products')])])
            ->withCount('products')
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'data' => CategoryResource::collection($categories),
        ]);
    }

    public function store(CategoryFormRequest $request): JsonResponse
    {
        $category = Category::create($request->validated());
        $category->load('children');

        return (new CategoryResource($category))
            ->response()
            ->setStatusCode(201);
    }

    public function update(CategoryFormRequest $request, Category $category): CategoryResource
    {
        $category->update($request->validated());
        $category->load(['children' => fn ($q) => $q->orderBy('sort_order')]);

        return new CategoryResource($category);
    }

    public function destroy(Category $category): JsonResponse
    {
        // Podkategorije ostaju (parent_id → null via nullOnDelete FK)
        $category->delete();

        return response()->json(['message' => 'Kategorija obrisana.']);
    }
}
