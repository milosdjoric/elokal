<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Look;
use Illuminate\Http\JsonResponse;

class LookController extends Controller
{
    public function index(): JsonResponse
    {
        $looks = Look::with(['hotspots.product' => fn ($q) => $q->where('is_active', true)->with('images')])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return response()->json(['data' => $looks]);
    }

    public function show(Look $look): JsonResponse
    {
        if (! $look->is_active) {
            abort(404);
        }

        $look->load(['hotspots.product' => fn ($q) => $q->where('is_active', true)->with('images')]);

        return response()->json(['data' => $look]);
    }
}
