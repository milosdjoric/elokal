<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\SearchLog;
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

    public function categories(Request $request): JsonResponse
    {
        $days = $request->input('days', 30);
        $from = now()->subDays($days);

        $categories = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('category_product', 'products.id', '=', 'category_product.product_id')
            ->join('categories', 'category_product.category_id', '=', 'categories.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.created_at', '>=', $from)
            ->whereNotIn('orders.status', ['cancelled', 'refunded'])
            ->select(
                'categories.id',
                'categories.name',
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.line_total) as total_revenue'),
                DB::raw('COUNT(DISTINCT orders.id) as orders_count'),
            )
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_revenue')
            ->get();

        return response()->json(['data' => $categories]);
    }

    public function coupons(Request $request): JsonResponse
    {
        $days = $request->input('days', 30);
        $from = now()->subDays($days);

        $coupons = DB::table('coupon_usages')
            ->join('coupons', 'coupon_usages.coupon_id', '=', 'coupons.id')
            ->where('coupon_usages.created_at', '>=', $from)
            ->select(
                'coupons.id',
                'coupons.code',
                'coupons.type',
                'coupons.value',
                DB::raw('COUNT(*) as times_used'),
                DB::raw('SUM(coupon_usages.discount_amount) as total_discount'),
            )
            ->groupBy('coupons.id', 'coupons.code', 'coupons.type', 'coupons.value')
            ->orderByDesc('times_used')
            ->get();

        // Prihod sa kuponom vs bez
        $revenueWithCoupon = Order::where('created_at', '>=', $from)
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->where('discount', '>', 0)
            ->sum('total');
        $revenueWithoutCoupon = Order::where('created_at', '>=', $from)
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->where('discount', 0)
            ->sum('total');

        return response()->json([
            'data' => $coupons,
            'revenue_with_coupon' => round((float) $revenueWithCoupon, 2),
            'revenue_without_coupon' => round((float) $revenueWithoutCoupon, 2),
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $days = $request->input('days', 30);
        $from = now()->subDays($days);

        $topSearches = SearchLog::where('created_at', '>=', $from)
            ->select('query', DB::raw('COUNT(*) as count'), DB::raw('AVG(results_count) as avg_results'))
            ->groupBy('query')
            ->orderByDesc('count')
            ->limit(20)
            ->get();

        $noResultSearches = SearchLog::where('created_at', '>=', $from)
            ->where('results_count', 0)
            ->select('query', DB::raw('COUNT(*) as count'))
            ->groupBy('query')
            ->orderByDesc('count')
            ->limit(20)
            ->get();

        $totalSearches = SearchLog::where('created_at', '>=', $from)->count();

        return response()->json([
            'top_searches' => $topSearches,
            'no_result_searches' => $noResultSearches,
            'total_searches' => $totalSearches,
        ]);
    }
}
