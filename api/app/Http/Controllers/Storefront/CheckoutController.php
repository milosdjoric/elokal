<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Coupon;
use App\Models\GiftCard;
use App\Models\LoyaltyAccount;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingMethod;
use App\Models\ShippingZone;
use App\Models\StockMovement;
use App\Models\StoreCreditAccount;
use App\Models\TaxRate;
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
            'shipping_method_id' => 'nullable|exists:shipping_methods,id',
            'gift_card_code' => 'nullable|string',
            'gift_card_amount' => 'nullable|numeric|min:0',
            'loyalty_points' => 'nullable|integer|min:0',
            'store_credits' => 'nullable|numeric|min:0',
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

                $product->decrement('stock_quantity', $item['quantity']);
                StockMovement::record($product->fresh(), -$item['quantity'], 'sale');
            }

            // Shipping
            $shippingCost = 0;
            $country = $request->input('shipping_country', 'RS');

            if ($request->filled('shipping_method_id')) {
                $shippingMethod = ShippingMethod::findOrFail($request->shipping_method_id);
                $shippingCost = $shippingMethod->calculateCost($subtotal);
            } else {
                // Auto-select: nađi zonu za državu, uzmi najjeftiniju aktivnu metodu
                $zone = ShippingZone::findForAddress($country, $request->shipping_postal_code);
                if ($zone) {
                    $cheapestMethod = $zone->methods()
                        ->where('is_active', true)
                        ->orderBy('cost')
                        ->first();
                    if ($cheapestMethod) {
                        $shippingCost = $cheapestMethod->calculateCost($subtotal);
                    }
                }
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

            // Gift card
            $giftCardDiscount = 0;
            $giftCard = null;
            if ($request->filled('gift_card_code')) {
                $giftCard = GiftCard::where('code', strtoupper($request->gift_card_code))
                    ->where('is_active', true)
                    ->first();
                if (! $giftCard || ! $giftCard->isValid()) {
                    throw ValidationException::withMessages(['gift_card_code' => ['Poklon kartica nije validna.']]);
                }
                $maxGiftAmount = (float) ($request->gift_card_amount ?? $giftCard->balance);
                $giftCardDiscount = min($maxGiftAmount, (float) $giftCard->balance, $subtotal + $shippingCost - $discount);
            }

            // Loyalty points
            $loyaltyDiscount = 0;
            if ($request->filled('loyalty_points') && $request->user()) {
                $loyaltyAccount = LoyaltyAccount::firstOrCreate(['user_id' => $request->user()->id]);
                $requestedPoints = (int) $request->loyalty_points;
                if ($requestedPoints > $loyaltyAccount->points_balance) {
                    throw ValidationException::withMessages(['loyalty_points' => ['Nemate dovoljno poena.']]);
                }
                // 1 poen = 1 RSD (konfigurisano)
                $loyaltyDiscount = min($requestedPoints, $subtotal + $shippingCost - $discount - $giftCardDiscount);
            }

            // Store credits
            $storeCreditDiscount = 0;
            if ($request->filled('store_credits') && $request->user()) {
                $creditAccount = StoreCreditAccount::firstOrCreate(['user_id' => $request->user()->id]);
                $requestedCredits = (float) $request->store_credits;
                if ($requestedCredits > (float) $creditAccount->balance) {
                    throw ValidationException::withMessages(['store_credits' => ['Nemate dovoljno kredita.']]);
                }
                $storeCreditDiscount = min($requestedCredits, $subtotal + $shippingCost - $discount - $giftCardDiscount - $loyaltyDiscount);
            }

            // Porez
            $taxRate = TaxRate::getForCountry($country);
            $tax = $taxRate ? $taxRate->calculateTax($subtotal) : 0;

            $total = max(0, $subtotal + $shippingCost + $tax - $discount - $giftCardDiscount - $loyaltyDiscount - $storeCreditDiscount);

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
                'shipping_country' => $country,
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'tax' => $tax,
                'discount' => $discount,
                'total' => $total,
                'notes' => $request->notes,
            ]);

            $order->items()->createMany($orderItems);

            // Dedukcija gift card
            if ($giftCard && $giftCardDiscount > 0) {
                $giftCard->transactions()->create([
                    'type' => 'debit',
                    'amount' => $giftCardDiscount,
                    'description' => "Narudžbina #{$order->order_number}",
                ]);
                $giftCard->decrement('balance', $giftCardDiscount);
            }

            // Dedukcija loyalty
            if ($loyaltyDiscount > 0 && $request->user()) {
                $loyaltyAccount->decrement('points_balance', (int) $loyaltyDiscount);
                $loyaltyAccount->transactions()->create([
                    'type' => 'redeem',
                    'points' => -(int) $loyaltyDiscount,
                    'description' => "Narudžbina #{$order->order_number}",
                ]);
            }

            // Dedukcija store credits
            if ($storeCreditDiscount > 0 && $request->user()) {
                $creditAccount->decrement('balance', $storeCreditDiscount);
                $creditAccount->transactions()->create([
                    'type' => 'debit',
                    'amount' => $storeCreditDiscount,
                    'description' => "Narudžbina #{$order->order_number}",
                ]);
            }

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

    public function calculateTax(Request $request): JsonResponse
    {
        $request->validate([
            'subtotal' => 'required|numeric|min:0',
            'country' => 'nullable|string|max:2',
        ]);

        $country = $request->input('country', 'RS');
        $taxRate = TaxRate::getForCountry($country);
        $tax = $taxRate ? $taxRate->calculateTax((float) $request->subtotal) : 0;

        return response()->json([
            'data' => [
                'tax' => $tax,
                'rate' => $taxRate?->rate ?? 0,
                'name' => $taxRate?->name ?? '',
            ],
        ]);
    }
}
