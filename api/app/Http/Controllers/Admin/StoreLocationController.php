<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StoreLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoreLocationController extends Controller
{
    public function index(): JsonResponse
    {
        $locations = StoreLocation::orderBy('sort_order')->get();

        return response()->json(['data' => $locations]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'string|max:5',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'working_hours' => 'nullable|array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $location = StoreLocation::create($data);

        return response()->json(['data' => $location], 201);
    }

    public function update(Request $request, StoreLocation $storeLocation): JsonResponse
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'address' => 'sometimes|string|max:500',
            'city' => 'sometimes|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'string|max:5',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'latitude' => 'sometimes|numeric|between:-90,90',
            'longitude' => 'sometimes|numeric|between:-180,180',
            'working_hours' => 'nullable|array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $storeLocation->update($data);

        return response()->json(['data' => $storeLocation]);
    }

    public function destroy(StoreLocation $storeLocation): JsonResponse
    {
        $storeLocation->delete();

        return response()->json(['message' => 'Lokacija obrisana.']);
    }
}
