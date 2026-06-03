<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::select('id', 'name', 'sku', 'stock_quantity', 'is_active', 'updated_at')
            ->orderBy('name');

        if ($request->boolean('low_stock')) {
            $query->where('stock_quantity', '<=', 5)->where('stock_quantity', '>', 0);
        }

        if ($request->boolean('out_of_stock')) {
            $query->where('stock_quantity', 0);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('sku', 'ilike', "%{$search}%");
            });
        }

        return response()->json($query->paginate(25));
    }

    public function adjust(Request $request, Product $product): JsonResponse
    {
        $request->validate([
            'quantity' => 'required|integer',
            'note' => 'nullable|string|max:500',
        ]);

        $product->increment('stock_quantity', $request->quantity);

        // Clamp to 0
        if ($product->stock_quantity < 0) {
            $product->update(['stock_quantity' => 0]);
        }

        StockMovement::record(
            $product->fresh(),
            $request->quantity,
            'adjustment',
            $request->note ?? 'Ručna korekcija',
            'admin',
            $request->user()->id,
        );

        return response()->json([
            'data' => [
                'stock_quantity' => $product->fresh()->stock_quantity,
            ],
        ]);
    }

    public function history(Product $product): JsonResponse
    {
        $movements = $product->stockMovements()
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        return response()->json(['data' => $movements]);
    }

    public function bulkAdjust(Request $request): JsonResponse
    {
        $request->validate([
            'adjustments' => 'required|array|min:1',
            'adjustments.*.product_id' => 'required|exists:products,id',
            'adjustments.*.quantity' => 'required|integer',
            'adjustments.*.note' => 'nullable|string|max:500',
        ]);

        $results = [];
        foreach ($request->adjustments as $adj) {
            $product = Product::findOrFail($adj['product_id']);
            $product->increment('stock_quantity', $adj['quantity']);

            if ($product->stock_quantity < 0) {
                $product->update(['stock_quantity' => 0]);
            }

            StockMovement::record(
                $product->fresh(),
                $adj['quantity'],
                'adjustment',
                $adj['note'] ?? 'Bulk korekcija',
                'admin',
                $request->user()->id,
            );

            $results[] = ['product_id' => $product->id, 'stock_quantity' => $product->fresh()->stock_quantity];
        }

        return response()->json(['data' => $results]);
    }

    public function export(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $products = Product::orderBy('name')->get(['id', 'name', 'sku', 'stock_quantity']);

        return response()->streamDownload(function () use ($products) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['id', 'name', 'sku', 'stock_quantity']);
            foreach ($products as $p) {
                fputcsv($handle, [$p->id, $p->name, $p->sku, $p->stock_quantity]);
            }
            fclose($handle);
        }, 'inventory-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv']);
    }

    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $handle = fopen($request->file('file')->getPathname(), 'r');
        $header = fgetcsv($handle);
        $skuIndex = array_search('sku', $header);
        $qtyIndex = array_search('stock_quantity', $header);

        if ($skuIndex === false || $qtyIndex === false) {
            fclose($handle);
            return response()->json(['message' => 'CSV mora imati kolone: sku, stock_quantity'], 422);
        }

        $updated = 0;
        while (($row = fgetcsv($handle)) !== false) {
            $sku = $row[$skuIndex] ?? null;
            $qty = (int) ($row[$qtyIndex] ?? 0);
            if (! $sku) continue;

            $product = Product::where('sku', $sku)->first();
            if (! $product) continue;

            $diff = $qty - $product->stock_quantity;
            $product->update(['stock_quantity' => $qty]);

            StockMovement::record($product, $diff, 'restock', 'CSV import', 'admin', $request->user()->id);
            $updated++;
        }
        fclose($handle);

        return response()->json(['message' => "{$updated} proizvoda ažurirano."]);
    }
}
