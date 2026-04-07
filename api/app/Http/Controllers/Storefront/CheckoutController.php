<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'phone' => 'nullable|string|max:30',
            'shipping_first_name' => 'required|string|max:255',
            'shipping_last_name' => 'required|string|max:255',
            'shipping_company' => 'nullable|string|max:255',
            'shipping_address_line_1' => 'required|string|max:255',
            'shipping_address_line_2' => 'nullable|string|max:255',
            'shipping_city' => 'required|string|max:255',
            'shipping_postal_code' => 'required|string|max:20',
            'shipping_country' => 'string|max:2',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'coupon_code' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request) {
            $subtotal = 0;
            $orderItems = [];

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);

                if (! $product->is_active) {
                    throw ValidationException::withMessages([
                        'items' => ["Proizvod '{$product->name}' nije dostupan."],
                    ]);
                }

                if ($product->stock_quantity < $item['quantity']) {
                    throw ValidationException::withMessages([
                        'items' => ["Nedovoljno na stanju: {$product->name} (dostupno: {$product->stock_quantity})."],
                    ]);
                }

                $price = $product->isSaleActive() ? $product->sale_price : $product->price;
                $lineTotal = $price * $item['quantity'];
                $subtotal += $lineTotal;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'price' => $price,
                    'quantity' => $item['quantity'],
                    'line_total' => $lineTotal,
                ];

                // Smanji stock
                $product->decrement('stock_quantity', $item['quantity']);
            }

            // Kupon
            $discount = 0;
            $coupon = null;
            if ($request->filled('coupon_code')) {
                $coupon = Coupon::where('code', strtoupper($request->coupon_code))->first();
                if (! $coupon) {
                    throw ValidationException::withMessages(['coupon_code' => ['Kupon ne postoji.']]);
                }
                $valid = $coupon->isValid($request->user()?->id);
                if ($valid !== true) {
                    throw ValidationException::withMessages(['coupon_code' => [$valid]]);
                }
                $discount = $coupon->calculateDiscount($subtotal);
            }

            $total = max(0, $subtotal - $discount);

            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $request->user()?->id,
                'status' => 'pending',
                'email' => $request->email,
                'phone' => $request->phone,
                'shipping_first_name' => $request->shipping_first_name,
                'shipping_last_name' => $request->shipping_last_name,
                'shipping_company' => $request->shipping_company,
                'shipping_address_line_1' => $request->shipping_address_line_1,
                'shipping_address_line_2' => $request->shipping_address_line_2,
                'shipping_city' => $request->shipping_city,
                'shipping_postal_code' => $request->shipping_postal_code,
                'shipping_country' => $request->shipping_country ?? 'RS',
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'notes' => $request->notes,
            ]);

            $order->items()->createMany($orderItems);

            // Evidentiranje kupona
            if ($coupon && $discount > 0) {
                $coupon->usages()->create([
                    'order_id' => $order->id,
                    'user_id' => $request->user()?->id,
                    'discount_amount' => $discount,
                ]);
                $coupon->increment('times_used');
            }

            $order->timeline()->create([
                'status' => 'pending',
                'note' => 'Narudžbina kreirana.',
                'actor_type' => 'system',
            ]);

            $order->load('items');

            return (new OrderResource($order))
                ->response()
                ->setStatusCode(201);
        });
    }
}
