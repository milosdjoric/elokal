<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function products(): StreamedResponse
    {
        $products = Product::with('categories')->orderBy('name')->get();

        return response()->streamDownload(function () use ($products) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['id', 'name', 'slug', 'sku', 'price', 'sale_price', 'stock_quantity', 'is_active', 'categories']);
            foreach ($products as $p) {
                fputcsv($handle, [
                    $p->id, $p->name, $p->slug, $p->sku,
                    $p->price, $p->sale_price, $p->stock_quantity,
                    $p->is_active ? 'yes' : 'no',
                    $p->categories->pluck('name')->implode(', '),
                ]);
            }
            fclose($handle);
        }, 'products-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv']);
    }

    public function orders(): StreamedResponse
    {
        $orders = Order::with('items')->orderByDesc('created_at')->get();

        return response()->streamDownload(function () use ($orders) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['order_number', 'status', 'email', 'name', 'city', 'subtotal', 'discount', 'total', 'items_count', 'created_at']);
            foreach ($orders as $o) {
                fputcsv($handle, [
                    $o->order_number, $o->status, $o->email,
                    "{$o->shipping_first_name} {$o->shipping_last_name}",
                    $o->shipping_city, $o->subtotal, $o->discount, $o->total,
                    $o->items->count(), $o->created_at->toDateTimeString(),
                ]);
            }
            fclose($handle);
        }, 'orders-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv']);
    }

    public function customers(): StreamedResponse
    {
        $customers = User::withCount('orders')
            ->withSum('orders', 'total')
            ->orderBy('name')
            ->get();

        return response()->streamDownload(function () use ($customers) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['id', 'name', 'email', 'phone', 'orders_count', 'total_spent', 'created_at']);
            foreach ($customers as $c) {
                fputcsv($handle, [
                    $c->id, $c->name, $c->email, $c->phone,
                    $c->orders_count, $c->orders_sum_total ?? 0,
                    $c->created_at->toDateTimeString(),
                ]);
            }
            fclose($handle);
        }, 'customers-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv']);
    }

    public function productTemplate(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['name', 'slug', 'sku', 'price', 'sale_price', 'stock_quantity', 'short_description', 'description', 'is_active']);
            fclose($handle);
        }, 'product-import-template.csv', ['Content-Type' => 'text/csv']);
    }
}
