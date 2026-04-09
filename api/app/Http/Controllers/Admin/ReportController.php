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
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    public function exportCsv(Request $request, string $type): StreamedResponse
    {
        $days = $request->input('days', 30);
        $from = now()->subDays($days);
        $date = now()->format('Y-m-d');

        return match ($type) {
            'sales' => $this->exportSalesCsv($from, $date),
            'products' => $this->exportTopProductsCsv($date),
            'customers' => $this->exportTopCustomersCsv($date),
            'categories' => $this->exportCategoriesCsv($from, $date),
            'coupons' => $this->exportCouponsCsv($from, $date),
            default => abort(404, 'Nepoznat tip izveštaja.'),
        };
    }

    private function exportSalesCsv($from, string $date): StreamedResponse
    {
        $sales = Order::where('created_at', '>=', $from)
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total) as revenue'), DB::raw('COUNT(*) as orders'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->streamDownload(function () use ($sales) {
            $h = fopen('php://output', 'w');
            fputcsv($h, ['Datum', 'Prihod', 'Narudžbine']);
            foreach ($sales as $row) {
                fputcsv($h, [$row->date, $row->revenue, $row->orders]);
            }
            fclose($h);
        }, "sales-{$date}.csv", ['Content-Type' => 'text/csv']);
    }

    private function exportTopProductsCsv(string $date): StreamedResponse
    {
        $products = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->select('products.name', 'products.sku',
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.line_total) as total_revenue'))
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderByDesc('total_revenue')
            ->get();

        return response()->streamDownload(function () use ($products) {
            $h = fopen('php://output', 'w');
            fputcsv($h, ['Proizvod', 'SKU', 'Prodato', 'Prihod']);
            foreach ($products as $row) {
                fputcsv($h, [$row->name, $row->sku, $row->total_sold, $row->total_revenue]);
            }
            fclose($h);
        }, "top-products-{$date}.csv", ['Content-Type' => 'text/csv']);
    }

    private function exportTopCustomersCsv(string $date): StreamedResponse
    {
        $customers = User::withCount('orders')
            ->withSum('orders', 'total')
            ->orderByDesc('orders_sum_total')
            ->get(['id', 'name', 'email']);

        return response()->streamDownload(function () use ($customers) {
            $h = fopen('php://output', 'w');
            fputcsv($h, ['Ime', 'Email', 'Narudžbine', 'Ukupno potrošeno']);
            foreach ($customers as $c) {
                fputcsv($h, [$c->name, $c->email, $c->orders_count, $c->orders_sum_total ?? 0]);
            }
            fclose($h);
        }, "top-customers-{$date}.csv", ['Content-Type' => 'text/csv']);
    }

    private function exportCategoriesCsv($from, string $date): StreamedResponse
    {
        $categories = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('category_product', 'products.id', '=', 'category_product.product_id')
            ->join('categories', 'category_product.category_id', '=', 'categories.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.created_at', '>=', $from)
            ->whereNotIn('orders.status', ['cancelled', 'refunded'])
            ->select('categories.name', DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.line_total) as total_revenue'),
                DB::raw('COUNT(DISTINCT orders.id) as orders_count'))
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_revenue')
            ->get();

        return response()->streamDownload(function () use ($categories) {
            $h = fopen('php://output', 'w');
            fputcsv($h, ['Kategorija', 'Prodato', 'Prihod', 'Narudžbine']);
            foreach ($categories as $c) {
                fputcsv($h, [$c->name, $c->total_sold, $c->total_revenue, $c->orders_count]);
            }
            fclose($h);
        }, "categories-{$date}.csv", ['Content-Type' => 'text/csv']);
    }

    private function exportCouponsCsv($from, string $date): StreamedResponse
    {
        $coupons = DB::table('coupon_usages')
            ->join('coupons', 'coupon_usages.coupon_id', '=', 'coupons.id')
            ->where('coupon_usages.created_at', '>=', $from)
            ->select('coupons.code', 'coupons.type', 'coupons.value',
                DB::raw('COUNT(*) as times_used'),
                DB::raw('SUM(coupon_usages.discount_amount) as total_discount'))
            ->groupBy('coupons.id', 'coupons.code', 'coupons.type', 'coupons.value')
            ->orderByDesc('times_used')
            ->get();

        return response()->streamDownload(function () use ($coupons) {
            $h = fopen('php://output', 'w');
            fputcsv($h, ['Kod', 'Tip', 'Vrednost', 'Korišćenja', 'Ukupan popust']);
            foreach ($coupons as $c) {
                fputcsv($h, [$c->code, $c->type, $c->value, $c->times_used, $c->total_discount]);
            }
            fclose($h);
        }, "coupons-{$date}.csv", ['Content-Type' => 'text/csv']);
    }
}
