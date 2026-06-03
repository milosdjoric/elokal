<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoyaltyAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoyaltyController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = LoyaltyAccount::with('user')->orderByDesc('points_earned_total');

        if ($request->filled('tier')) {
            $query->where('tier', $request->tier);
        }

        return response()->json($query->paginate(15));
    }

    public function show(LoyaltyAccount $account): JsonResponse
    {
        return response()->json([
            'data' => $account->load(['user', 'transactions']),
            'next_tier' => $account->next_tier,
        ]);
    }

    public function adjust(Request $request, LoyaltyAccount $account): JsonResponse
    {
        $request->validate([
            'points' => 'required|integer',
            'description' => 'nullable|string|max:500',
        ]);

        if ($request->points > 0) {
            $account->addPoints($request->points, 'adjustment', $request->description ?? 'Ručna korekcija.');
        } else {
            $account->decrement('points_balance', abs($request->points));
            $account->transactions()->create([
                'points' => $request->points,
                'type' => 'adjustment',
                'description' => $request->description ?? 'Ručna korekcija.',
            ]);
        }

        return response()->json(['data' => $account->fresh()]);
    }

    public function config(): JsonResponse
    {
        return response()->json([
            'tiers' => LoyaltyAccount::TIERS,
            'earn_rules' => [
                // TODO(CORE-002 1b): rate brojevi (100/50) ostaju hardkodovani — externalizovati
                // u settings ako loyalty stope treba da budu per-klijent (dira i checkout logiku).
                'purchase' => '1 poen = 100 ' . currency_symbol(),
                'registration' => '100 poena',
                'review' => '50 poena',
            ],
            'redeem_rate' => '100 poena = 100 ' . currency_symbol(),
        ]);
    }
}
