<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductCollection;
use App\Models\Category;
use App\Models\Product;
use App\Models\SearchLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request): ProductCollection
    {
        $request->validate([
            'q' => 'required|string|min:2|max:100',
        ]);

        $search = $request->q;

        $query = Product::with(['categories', 'images'])
            ->where('is_active', true)
            ->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('short_description', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });

        // Category scope filter
        if ($request->filled('category')) {
            $query->whereHas('categories', fn ($q) => $q->where('categories.id', $request->category));
        }

        // Sortiranje
        $sort = $request->input('sort', 'relevance');
        $query = match ($sort) {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'created_at' => $query->orderByDesc('created_at'),
            'name' => $query->orderBy('name'),
            default => $query->orderByDesc('id'), // relevance — najnoviji prvo
        };

        $perPage = min($request->input('per_page', 12), 48);
        $results = $query->paginate($perPage);

        SearchLog::create([
            'query' => $search,
            'results_count' => $results->total(),
            'user_id' => $request->user()?->id,
            'ip' => $request->ip(),
        ]);

        // Matching kategorije za autocomplete
        $matchingCategories = Category::where('is_active', true)
            ->where('name', 'like', "%{$search}%")
            ->select('id', 'name', 'slug')
            ->limit(5)
            ->get();

        return (new ProductCollection($results))
            ->additional(['matching_categories' => $matchingCategories]);
    }
}
