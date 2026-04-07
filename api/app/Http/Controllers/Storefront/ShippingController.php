<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\ShippingMethod;
use App\Models\ShippingZone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShippingController extends Controller
{
    public function methods(Request $request): JsonResponse
    {
        $request->validate([
            'country' => 'required|string|size:2',
            'postal_code' => 'nullable|string',
            'subtotal' => 'required|numeric|min:0',
        ]);

        $zone = ShippingZone::findForAddress($request->country, $request->postal_code);

        if (! $zone) {
            return response()->json(['data' => [], 'message' => 'Nema dostupnih metoda dostave za vašu lokaciju.']);
        }

        $methods = $zone->methods()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function ($method) use ($request) {
                return [
                    'id' => $method->id,
                    'name' => $method->name,
                    'type' => $method->type,
                    'cost' => number_format($method->calculateCost((float) $request->subtotal), 2, '.', ''),
                    'free_above' => $method->free_above,
                    'estimated_days' => $method->estimated_days,
                ];
            });

        return response()->json(['data' => $methods]);
    }

    /**
     * Vraća shipping config za default zonu (RS).
     * Koristi se na celom sajtu: top bar, korpa, checkout.
     */
    public function config(): JsonResponse
    {
        $zone = ShippingZone::findForAddress('RS');

        if (! $zone) {
            return response()->json([
                'data' => [
                    'free_shipping_threshold' => null,
                    'default_shipping_cost' => null,
                    'methods' => [],
                ],
            ]);
        }

        $methods = $zone->methods()->where('is_active', true)->orderBy('sort_order')->get();

        // Prag za besplatno = free_above najjeftinije metode koja ima taj prag
        $freeThreshold = $methods->whereNotNull('free_above')->min('free_above');

        // Default cena = cena najjeftinije metode
        $defaultCost = $methods->min('cost');

        return response()->json([
            'data' => [
                'free_shipping_threshold' => $freeThreshold ? (float) $freeThreshold : null,
                'default_shipping_cost' => $defaultCost ? (float) $defaultCost : null,
                'methods' => $methods->map(fn ($m) => [
                    'id' => $m->id,
                    'name' => $m->name,
                    'cost' => (float) $m->cost,
                    'free_above' => $m->free_above ? (float) $m->free_above : null,
                    'estimated_days' => $m->estimated_days,
                ]),
            ],
        ]);
    }
}
