<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Refund;
use App\Models\StoreCreditAccount;
use App\Models\StockMovement;
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
        $order->load(['items', 'timeline', 'refunds.creator', 'shipments']);
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
                if ($item->product_id && $item->product) {
                    $item->product->increment('stock_quantity', $item->quantity);
                    StockMovement::record($item->product->fresh(), $item->quantity, 'cancellation', "Order #{$order->order_number}", 'order', $order->id);
                }
            }
        }

        $order->load(['items', 'timeline']);
        return new OrderResource($order);
    }

    public function refund(Request $request, Order $order): OrderResource
    {
        $maxRefundable = bcsub($order->total, $order->refunded_amount, 2);

        $request->validate([
            'amount' => "required|numeric|min:0.01|max:{$maxRefundable}",
            'reason' => 'nullable|string|max:1000',
            'method' => 'required|in:original,store_credit',
        ]);

        $amount = $request->amount;
        $newRefundedAmount = bcadd($order->refunded_amount, $amount, 2);
        $isFullRefund = bccomp($newRefundedAmount, $order->total, 2) === 0;

        // Kreiraj Refund zapis
        Refund::create([
            'order_id' => $order->id,
            'amount' => $amount,
            'method' => $request->method,
            'reason' => $request->reason,
            'created_by' => $request->user()->id,
        ]);

        // Ažuriraj order
        $oldStatus = $order->status;
        $order->update([
            'refunded_amount' => $newRefundedAmount,
            'refund_reason' => $request->reason ?? $order->refund_reason,
            'status' => $isFullRefund ? 'refunded' : $order->status,
        ]);

        // Ažuriraj Payment status pri full refundu
        if ($isFullRefund) {
            $order->payments()->where('status', 'completed')->update(['status' => 'refunded']);
        }

        // Store credit: uplati na korisnikov račun
        if ($request->method === 'store_credit' && $order->user_id) {
            $account = StoreCreditAccount::firstOrCreate(
                ['user_id' => $order->user_id],
                ['balance' => 0],
            );
            $account->credit($amount, "Refund za narudžbinu #{$order->order_number}", $order->id);
        }

        // Timeline log
        $methodLabel = $request->method === 'store_credit' ? 'store credit' : 'original';
        $order->timeline()->create([
            'status' => $order->status,
            'old_status' => $isFullRefund ? $oldStatus : $order->status,
            'note' => "Refund: {$amount} RSD ({$methodLabel})" . ($request->reason ? " — {$request->reason}" : ''),
            'actor_type' => 'admin',
            'actor_id' => $request->user()->id,
        ]);

        // Vrati stock na full refund
        if ($isFullRefund) {
            foreach ($order->items as $item) {
                if ($item->product_id) {
                    $item->product?->increment('stock_quantity', $item->quantity);
                }
            }
        }

        $order->load(['items', 'timeline', 'refunds']);
        return new OrderResource($order);
    }

    public function update(Request $request, Order $order): OrderResource
    {
        if (! in_array($order->status, ['pending', 'processing'])) {
            abort(422, 'Narudžbina se može menjati samo u statusu "Na čekanju" ili "U obradi".');
        }

        $request->validate([
            'email' => 'sometimes|email',
            'phone' => 'nullable|string|max:30',
            'shipping_first_name' => 'sometimes|string|max:255',
            'shipping_last_name' => 'sometimes|string|max:255',
            'shipping_company' => 'nullable|string|max:255',
            'shipping_address_line_1' => 'sometimes|string|max:255',
            'shipping_address_line_2' => 'nullable|string|max:255',
            'shipping_city' => 'sometimes|string|max:255',
            'shipping_postal_code' => 'sometimes|string|max:20',
            'shipping_country' => 'sometimes|string|max:2',
            'items' => 'sometimes|array|min:1',
            'items.*.product_id' => 'required_with:items|exists:products,id',
            'items.*.quantity' => 'required_with:items|integer|min:1',
        ]);

        // Ažuriraj adresu/kontakt
        $order->update($request->only([
            'email', 'phone',
            'shipping_first_name', 'shipping_last_name', 'shipping_company',
            'shipping_address_line_1', 'shipping_address_line_2',
            'shipping_city', 'shipping_postal_code', 'shipping_country',
        ]));

        // Ažuriraj stavke ako su prosleđene
        if ($request->has('items')) {
            // Vrati stock za stare stavke
            foreach ($order->items as $item) {
                if ($item->product_id) {
                    $item->product?->increment('stock_quantity', $item->quantity);
                }
            }
            $order->items()->delete();

            $subtotal = 0;
            foreach ($request->items as $itemData) {
                $product = \App\Models\Product::findOrFail($itemData['product_id']);
                $price = $product->isSaleActive() ? $product->sale_price : $product->price;
                $lineTotal = $price * $itemData['quantity'];
                $subtotal += $lineTotal;

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'price' => $price,
                    'quantity' => $itemData['quantity'],
                    'line_total' => $lineTotal,
                ]);

                $product->decrement('stock_quantity', $itemData['quantity']);
            }

            $order->update([
                'subtotal' => $subtotal,
                'total' => $subtotal + $order->shipping_cost - $order->discount,
            ]);
        }

        // Timeline log
        $order->timeline()->create([
            'status' => $order->status,
            'old_status' => $order->status,
            'note' => 'Narudžbina izmenjena.',
            'actor_type' => 'admin',
            'actor_id' => $request->user()->id,
        ]);

        $order->load(['items', 'timeline']);
        return new OrderResource($order);
    }

    public function updateTracking(Request $request, Order $order): OrderResource
    {
        $request->validate([
            'tracking_number' => 'nullable|string|max:100',
            'tracking_carrier' => 'nullable|string|max:100',
            'tracking_url' => 'nullable|url|max:500',
        ]);

        $order->update($request->only(['tracking_number', 'tracking_carrier', 'tracking_url']));

        // Timeline log
        if ($request->tracking_number) {
            $order->timeline()->create([
                'status' => $order->status,
                'old_status' => $order->status,
                'note' => "Tracking dodat: {$request->tracking_carrier} — {$request->tracking_number}",
                'actor_type' => 'admin',
                'actor_id' => $request->user()->id,
            ]);
        }

        $order->load(['items', 'timeline']);
        return new OrderResource($order);
    }
}
