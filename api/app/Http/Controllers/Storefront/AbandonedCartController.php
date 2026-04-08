<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\AbandonedCart;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AbandonedCartController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        if (! feature('feature_abandoned_cart')) {
            return response()->json(['message' => 'Feature disabled.'], 403);
        }

        $request->validate([
            'email' => 'required|email',
            'items' => 'required|array|min:1',
            'total' => 'required|numeric|min:0',
        ]);

        AbandonedCart::updateOrCreate(
            ['email' => $request->email, 'status' => 'abandoned'],
            [
                'user_id' => $request->user()?->id,
                'items' => $request->items,
                'total' => $request->total,
                'token' => AbandonedCart::generateToken(),
            ],
        );

        return response()->json(['message' => 'OK']);
    }

    public function recover(string $token): JsonResponse
    {
        $cart = AbandonedCart::where('token', $token)
            ->where('status', 'abandoned')
            ->first();

        if (! $cart) {
            return response()->json(['message' => 'Link je istekao ili korpa je već obnovljena.'], 404);
        }

        return response()->json([
            'data' => [
                'items' => $cart->items,
                'total' => $cart->total,
            ],
        ]);
    }

    public function markRecovered(string $token): JsonResponse
    {
        $cart = AbandonedCart::where('token', $token)->first();

        if ($cart) {
            $cart->update([
                'status' => 'recovered',
                'recovered_at' => now(),
            ]);
        }

        return response()->json(['message' => 'OK']);
    }
}
