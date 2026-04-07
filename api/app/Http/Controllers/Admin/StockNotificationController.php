<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockNotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = StockNotification::with('product')->orderByDesc('created_at');

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->boolean('pending_only')) {
            $query->pending();
        }

        return response()->json($query->paginate(15));
    }

    public function byProduct(int $productId): JsonResponse
    {
        $notifications = StockNotification::where('product_id', $productId)
            ->pending()
            ->get();

        return response()->json([
            'data' => $notifications,
            'count' => $notifications->count(),
        ]);
    }
}
