<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
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
                    'estimated_days' => $method->estimated_days,
                ];
            });

        return response()->json(['data' => $methods]);
    }
}
