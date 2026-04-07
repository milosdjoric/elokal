<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbandonedCart;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AbandonedCartController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = AbandonedCart::orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->paginate(15));
    }

    public function stats(): JsonResponse
    {
        $abandoned = AbandonedCart::where('status', 'abandoned')->count();
        $recovered = AbandonedCart::where('status', 'recovered')->count();
        $total = $abandoned + $recovered;

        return response()->json([
            'abandoned' => $abandoned,
            'recovered' => $recovered,
            'recovery_rate' => $total > 0 ? round(($recovered / $total) * 100, 1) : 0,
            'total_value_abandoned' => AbandonedCart::where('status', 'abandoned')->sum('total'),
            'total_value_recovered' => AbandonedCart::where('status', 'recovered')->sum('total'),
        ]);
    }
}
