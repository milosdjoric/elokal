<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Look;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LookController extends Controller
{
    public function index(): JsonResponse
    {
        $looks = Look::with(['hotspots.product:id,name,slug,price,effective_price'])
            ->orderBy('sort_order')
            ->get();

        return response()->json(['data' => $looks]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'image_path' => 'required|string',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'hotspots' => 'nullable|array',
            'hotspots.*.product_id' => 'required|exists:products,id',
            'hotspots.*.x_position' => 'required|numeric|min:0|max:100',
            'hotspots.*.y_position' => 'required|numeric|min:0|max:100',
            'hotspots.*.label' => 'nullable|string|max:255',
        ]);

        $look = Look::create(collect($data)->except('hotspots')->toArray());

        if (! empty($data['hotspots'])) {
            $look->hotspots()->createMany($data['hotspots']);
        }

        $look->load('hotspots.product:id,name,slug,price');

        return response()->json(['data' => $look], 201);
    }

    public function update(Request $request, Look $look): JsonResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'image_path' => 'required|string',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'hotspots' => 'nullable|array',
            'hotspots.*.product_id' => 'required|exists:products,id',
            'hotspots.*.x_position' => 'required|numeric|min:0|max:100',
            'hotspots.*.y_position' => 'required|numeric|min:0|max:100',
            'hotspots.*.label' => 'nullable|string|max:255',
        ]);

        $look->update(collect($data)->except('hotspots')->toArray());

        // Sync hotspots — obriši stare, kreiraj nove
        $look->hotspots()->delete();
        if (! empty($data['hotspots'])) {
            $look->hotspots()->createMany($data['hotspots']);
        }

        $look->load('hotspots.product:id,name,slug,price');

        return response()->json(['data' => $look]);
    }

    public function destroy(Look $look): JsonResponse
    {
        $look->delete();

        return response()->json(['message' => 'Look obrisan.']);
    }
}
