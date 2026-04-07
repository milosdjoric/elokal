<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GiftCard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GiftCardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = GiftCard::orderByDesc('created_at');

        if ($request->filled('search')) {
            $query->where('code', 'like', "%{$request->search}%");
        }
        if ($request->filled('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        return response()->json($query->paginate(15));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'initial_amount' => 'required|numeric|min:1',
            'recipient_email' => 'nullable|email',
            'recipient_name' => 'nullable|string|max:255',
            'message' => 'nullable|string|max:1000',
            'expires_at' => 'nullable|date|after:today',
        ]);

        $card = GiftCard::create([
            ...$data,
            'code' => GiftCard::generateCode(),
            'balance' => $data['initial_amount'],
        ]);

        $card->transactions()->create([
            'amount' => $data['initial_amount'],
            'type' => 'purchase',
            'balance_after' => $data['initial_amount'],
            'note' => 'Kreirana od strane admina.',
        ]);

        return response()->json(['data' => $card], 201);
    }

    public function show(GiftCard $giftCard): JsonResponse
    {
        return response()->json([
            'data' => $giftCard->load('transactions'),
        ]);
    }

    public function update(Request $request, GiftCard $giftCard): JsonResponse
    {
        $data = $request->validate([
            'is_active' => 'boolean',
            'expires_at' => 'nullable|date',
        ]);

        $giftCard->update($data);
        return response()->json(['data' => $giftCard]);
    }

    public function adjust(Request $request, GiftCard $giftCard): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric',
            'note' => 'nullable|string|max:500',
        ]);

        $giftCard->increment('balance', $request->amount);
        if ((float) $giftCard->balance < 0) {
            $giftCard->update(['balance' => 0]);
        }

        $giftCard->transactions()->create([
            'amount' => $request->amount,
            'type' => 'adjustment',
            'balance_after' => $giftCard->fresh()->balance,
            'note' => $request->note ?? 'Ručna korekcija.',
        ]);

        return response()->json(['data' => $giftCard->fresh()]);
    }
}
