<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ScheduledExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $type = 'all', // products, orders, customers, all
    ) {}

    public function handle(): void
    {
        $date = now()->format('Y-m-d');
        $types = $this->type === 'all' ? ['products', 'orders', 'customers'] : [$this->type];
        $generated = [];

        foreach ($types as $type) {
            $path = "exports/{$type}-{$date}.csv";
            $csv = match ($type) {
                'products' => $this->generateProducts(),
                'orders' => $this->generateOrders(),
                'customers' => $this->generateCustomers(),
            };
            Storage::disk('local')->put($path, $csv);
            $generated[] = $path;
        }

        // TODO: Slanje emaila sa attachment-ima kad bude SMTP konfigurisan
        // $adminEmail = Setting::getValue('general_email');
        // Mail::to($adminEmail)->send(new ScheduledExportMail($generated));

        Log::info('ScheduledExport: generisani fajlovi', ['files' => $generated]);
    }

    private function generateProducts(): string
    {
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, ['id', 'name', 'slug', 'sku', 'price', 'sale_price', 'stock_quantity', 'is_active', 'categories']);

        Product::with('categories')->orderBy('name')->chunk(500, function ($products) use ($handle) {
            foreach ($products as $p) {
                fputcsv($handle, [
                    $p->id, $p->name, $p->slug, $p->sku,
                    $p->price, $p->sale_price, $p->stock_quantity,
                    $p->is_active ? 'yes' : 'no',
                    $p->categories->pluck('name')->implode(', '),
                ]);
            }
        });

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);
        return $csv;
    }

    private function generateOrders(): string
    {
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, ['order_number', 'status', 'email', 'name', 'city', 'subtotal', 'discount', 'total', 'items_count', 'created_at']);

        Order::with('items')->orderByDesc('created_at')->chunk(500, function ($orders) use ($handle) {
            foreach ($orders as $o) {
                fputcsv($handle, [
                    $o->order_number, $o->status, $o->email,
                    "{$o->shipping_first_name} {$o->shipping_last_name}",
                    $o->shipping_city, $o->subtotal, $o->discount, $o->total,
                    $o->items->count(), $o->created_at->toDateTimeString(),
                ]);
            }
        });

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);
        return $csv;
    }

    private function generateCustomers(): string
    {
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, ['id', 'name', 'email', 'phone', 'orders_count', 'total_spent', 'created_at']);

        User::withCount('orders')
            ->withSum('orders', 'total')
            ->orderBy('name')
            ->chunk(500, function ($customers) use ($handle) {
                foreach ($customers as $c) {
                    fputcsv($handle, [
                        $c->id, $c->name, $c->email, $c->phone,
                        $c->orders_count, $c->orders_sum_total ?? 0,
                        $c->created_at->toDateTimeString(),
                    ]);
                }
            });

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);
        return $csv;
    }
}
