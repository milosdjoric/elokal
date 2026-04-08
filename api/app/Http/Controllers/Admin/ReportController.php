<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function overview(Request $request): JsonResponse
    {
        $days = $request->input('days', 30);
        $from = now()->subDays($days);

        $revenue = Order::where('created_at', '>=', $from)
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->sum('total');

        $ordersCount = Order::where('created_at', '>=', $from)->count();
        $aov = $ordersCount > 0 ? $revenue / $ordersCount : 0;

        $newCustomers = User::where('created_at', '>=', $from)->count();
        $totalCustomers = User::count();

        $pendingOrders = Order::where('status', 'pending')->count();
        $lowStock = Product::where('stock_quantity', '>', 0)->where('stock_quantity', '<=', 5)->count();
        $outOfStock = Product::where('stock_quantity', 0)->count();
        $pendingReviews = Review::where('status', 'pending')->count();

        return response()->json([
            'revenue' => round($revenue, 2),
            'orders_count' => $ordersCount,
            'aov' => round($aov, 2),
            'new_customers' => $newCustomers,
            'total_customers' => $totalCustomers,
            'pending_orders' => $pendingOrders,
            'low_stock' => $lowStock,
            'out_of_stock' => $outOfStock,
            'pending_reviews' => $pendingReviews,
        ]);
    }

    public function salesByDay(Request $request): JsonResponse
    {
        $days = $request->input('days', 30);
        $from = now()->subDays($days)->startOfDay();

        $sales = Order::where('created_at', '>=', $from)
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total) as revenue'), DB::raw('COUNT(*) as orders'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json(['data' => $sales]);
    }

    public function topProducts(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 10);

        $products = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->select('products.id', 'products.name', 'products.sku',
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.line_total) as total_revenue'))
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderByDesc('total_revenue')
            ->limit($limit)
            ->get();

        return response()->json(['data' => $products]);
    }

    public function topCustomers(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 10);

        $customers = User::withCount('orders')
            ->withSum('orders', 'total')
            ->orderByDesc('orders_sum_total')
            ->limit($limit)
            ->get(['id', 'name', 'email']);

        return response()->json(['data' => $customers]);
    }
}
