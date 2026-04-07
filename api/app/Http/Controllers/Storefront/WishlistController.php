<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $productIds = $request->user()->wishlists()->pluck('product_id');

        $products = Product::whereIn('id', $productIds)
            ->where('is_active', true)
            ->with('images')
            ->get();

        return response()->json([
            'data' => \App\Http\Resources\ProductResource::collection($products),
        ]);
    }

    public function store(Request $request, Product $product): JsonResponse
    {
        $request->user()->wishlists()->firstOrCreate([
            'product_id' => $product->id,
        ]);

        return response()->json(['message' => 'Dodato u listu želja.'], 201);
    }

    public function destroy(Request $request, Product $product): JsonResponse
    {
        $request->user()->wishlists()->where('product_id', $product->id)->delete();

        return response()->json(['message' => 'Uklonjeno iz liste želja.']);
    }

    public function ids(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $request->user()->wishlists()->pluck('product_id'),
        ]);
    }

    public function sync(Request $request): JsonResponse
    {
        $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:products,id',
        ]);

        foreach ($request->product_ids as $productId) {
            $request->user()->wishlists()->firstOrCreate([
                'product_id' => $productId,
            ]);
        }

        return response()->json([
            'data' => $request->user()->wishlists()->pluck('product_id'),
        ]);
    }
}
