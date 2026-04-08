<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'total_products' => Product::count(),
            'active_products' => Product::where('is_active', true)->count(),
            'total_categories' => Category::count(),
            'featured_products' => Product::where('featured', true)->count(),
            'out_of_stock' => Product::where('stock_quantity', 0)->count(),
        ]);
    }

    public function lowStock(): JsonResponse
    {
        $products = Product::where('is_active', true)
            ->where('stock_quantity', '>', 0)
            ->where('stock_quantity', '<=', 10)
            ->orderBy('stock_quantity')
            ->limit(15)
            ->select('id', 'name', 'sku', 'stock_quantity')
            ->get();

        return response()->json(['data' => $products]);
    }
}
