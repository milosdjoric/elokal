<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\CallbackRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CallbackRequestController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'nullable|exists:products,id',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:30',
            'channel' => 'required|in:' . implode(',', CallbackRequest::CHANNELS),
            'message' => 'nullable|string|max:1000',
        ]);

        CallbackRequest::create($request->only([
            'product_id', 'name', 'phone', 'channel', 'message',
        ]));

        return response()->json([
            'message' => 'Zahtev primljen. Kontaktiraćemo vas u najkraćem roku.',
        ], 201);
    }
}
