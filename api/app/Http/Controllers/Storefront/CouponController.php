<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function validate(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string',
            'subtotal' => 'required|numeric|min:0',
        ]);

        $coupon = Coupon::where('code', strtoupper($request->code))->first();

        if (! $coupon) {
            return response()->json(['message' => 'Kupon ne postoji.'], 422);
        }

        $valid = $coupon->isValid($request->user()?->id);
        if ($valid !== true) {
            return response()->json(['message' => $valid], 422);
        }

        if ($coupon->min_order_amount && $request->subtotal < (float) $coupon->min_order_amount) {
            return response()->json([
                'message' => "Minimalan iznos narudžbine za ovaj kupon je {$coupon->min_order_amount} " . currency_symbol() . '.',
            ], 422);
        }

        $discount = $coupon->calculateDiscount((float) $request->subtotal);

        return response()->json([
            'data' => [
                'code' => $coupon->code,
                'type' => $coupon->type,
                'value' => $coupon->value,
                'discount' => number_format($discount, 2, '.', ''),
                'description' => $coupon->description,
            ],
        ]);
    }
}
