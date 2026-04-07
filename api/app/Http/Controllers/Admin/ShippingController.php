<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingMethod;
use App\Models\ShippingZone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShippingController extends Controller
{
    // --- Zones ---

    public function zones(): JsonResponse
    {
        $zones = ShippingZone::with('methods')->orderBy('sort_order')->get();

        return response()->json(['data' => $zones]);
    }

    public function storeZone(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'countries' => 'required|array|min:1',
            'countries.*' => 'string|size:2',
            'postal_codes' => 'nullable|array',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $zone = ShippingZone::create($data);

        return response()->json(['data' => $zone->load('methods')], 201);
    }

    public function updateZone(Request $request, ShippingZone $zone): JsonResponse
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'countries' => 'sometimes|array|min:1',
            'countries.*' => 'string|size:2',
            'postal_codes' => 'nullable|array',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $zone->update($data);

        return response()->json(['data' => $zone->load('methods')]);
    }

    public function destroyZone(ShippingZone $zone): JsonResponse
    {
        $zone->delete();

        return response()->json(['message' => 'Zona obrisana.']);
    }

    // --- Methods ---

    public function storeMethod(Request $request, ShippingZone $zone): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:' . implode(',', ShippingMethod::TYPES),
            'cost' => 'numeric|min:0',
            'free_above' => 'nullable|numeric|min:0',
            'per_kg_cost' => 'nullable|numeric|min:0',
            'estimated_days' => 'nullable|string|max:20',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $method = $zone->methods()->create($data);

        return response()->json(['data' => $method], 201);
    }

    public function updateMethod(Request $request, ShippingMethod $method): JsonResponse
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|in:' . implode(',', ShippingMethod::TYPES),
            'cost' => 'numeric|min:0',
            'free_above' => 'nullable|numeric|min:0',
            'per_kg_cost' => 'nullable|numeric|min:0',
            'estimated_days' => 'nullable|string|max:20',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $method->update($data);

        return response()->json(['data' => $method]);
    }

    public function destroyMethod(ShippingMethod $method): JsonResponse
    {
        $method->delete();

        return response()->json(['message' => 'Metoda obrisana.']);
    }
}
