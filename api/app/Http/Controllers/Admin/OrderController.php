<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Order::with('items')->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('shipping_first_name', 'like', "%{$search}%")
                  ->orWhere('shipping_last_name', 'like', "%{$search}%");
            });
        }

        $perPage = min($request->input('per_page', 15), 100);

        return response()->json(
            OrderResource::collection($query->paginate($perPage))
                ->response()
                ->getData(true)
        );
    }

    public function show(Order $order): OrderResource
    {
        $order->load(['items', 'timeline']);
        return new OrderResource($order);
    }

    public function updateStatus(Request $request, Order $order): OrderResource
    {
        $request->validate([
            'status' => 'required|in:' . implode(',', Order::STATUSES),
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $oldStatus = $order->status;

        $order->update([
            'status' => $request->status,
            'admin_notes' => $request->admin_notes ?? $order->admin_notes,
        ]);

        // Timeline log
        $order->timeline()->create([
            'status' => $request->status,
            'old_status' => $oldStatus,
            'note' => $request->admin_notes,
            'actor_type' => 'admin',
            'actor_id' => $request->user()->id,
        ]);

        // Vrati stock ako cancelled
        if ($request->status === 'cancelled' && $oldStatus !== 'cancelled') {
            foreach ($order->items as $item) {
                if ($item->product_id) {
                    $item->product?->increment('stock_quantity', $item->quantity);
                }
            }
        }

        $order->load(['items', 'timeline']);
        return new OrderResource($order);
    }
}
