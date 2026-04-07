<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockNotificationController extends Controller
{
    public function store(Request $request, Product $product): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|max:255',
        ]);

        if ($product->stock_quantity > 0) {
            return response()->json(['message' => 'Proizvod je već na stanju.'], 422);
        }

        StockNotification::firstOrCreate([
            'product_id' => $product->id,
            'email' => $request->email,
        ]);

        return response()->json([
            'message' => 'Obavestićemo vas kada proizvod bude ponovo na stanju.',
        ], 201);
    }
}
