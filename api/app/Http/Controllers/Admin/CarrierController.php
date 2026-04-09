<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Carrier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CarrierController extends Controller
{
    public function index(): JsonResponse
    {
        $carriers = Carrier::orderBy('sort_order')->get();

        return response()->json(['data' => $carriers]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|max:50|unique:carriers',
            'name' => 'required|string|max:255',
            'tracking_url_pattern' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $carrier = Carrier::create($request->only(['code', 'name', 'tracking_url_pattern', 'is_active', 'sort_order']));

        return response()->json(['data' => $carrier], 201);
    }

    public function update(Request $request, Carrier $carrier): JsonResponse
    {
        $request->validate([
            'code' => "sometimes|string|max:50|unique:carriers,code,{$carrier->id}",
            'name' => 'sometimes|string|max:255',
            'tracking_url_pattern' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $carrier->update($request->only(['code', 'name', 'tracking_url_pattern', 'is_active', 'sort_order']));

        return response()->json(['data' => $carrier]);
    }

    public function destroy(Carrier $carrier): JsonResponse
    {
        $carrier->delete();

        return response()->json(['message' => 'Kurir obrisan.']);
    }
}
