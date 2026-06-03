<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\StoreLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoreLocationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if (! feature('feature_store_locator')) {
            abort(404);
        }

        $query = StoreLocation::active()->orderBy('sort_order');

        // Pretraga po gradu
        if ($request->filled('city')) {
            $query->where('city', 'ilike', "%{$request->city}%");
        }

        // Pretraga po radijusu
        if ($request->filled('lat') && $request->filled('lng')) {
            $radius = $request->input('radius', 50);
            $query->nearby((float) $request->lat, (float) $request->lng, (float) $radius);
        }

        return response()->json(['data' => $query->get()]);
    }
}
