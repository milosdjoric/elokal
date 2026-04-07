<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => PaymentMethod::orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code' => 'required|string|max:50|unique:payment_methods',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'instructions' => 'nullable|string|max:2000',
            'additional_cost' => 'numeric|min:0',
            'is_active' => 'boolean',
            'is_online' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        return response()->json(['data' => PaymentMethod::create($data)], 201);
    }

    public function update(Request $request, PaymentMethod $paymentMethod): JsonResponse
    {
        $data = $request->validate([
            'code' => "sometimes|string|max:50|unique:payment_methods,code,{$paymentMethod->id}",
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:1000',
            'instructions' => 'nullable|string|max:2000',
            'additional_cost' => 'numeric|min:0',
            'is_active' => 'boolean',
            'is_online' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $paymentMethod->update($data);

        return response()->json(['data' => $paymentMethod]);
    }

    public function destroy(PaymentMethod $paymentMethod): JsonResponse
    {
        $paymentMethod->delete();

        return response()->json(['message' => 'Metoda plaćanja obrisana.']);
    }

    public function transactions(Request $request): JsonResponse
    {
        $query = Payment::with(['order', 'method'])->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->paginate(15));
    }
}
