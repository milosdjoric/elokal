<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StoreCreditAccount;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoreCreditController extends Controller
{
    public function index(): JsonResponse
    {
        $accounts = StoreCreditAccount::with('user')
            ->where('balance', '>', 0)
            ->orderByDesc('balance')
            ->paginate(15);

        return response()->json($accounts);
    }

    public function show(StoreCreditAccount $account): JsonResponse
    {
        return response()->json([
            'data' => $account->load(['user', 'transactions']),
        ]);
    }

    public function adjust(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric',
            'reason' => 'required|string|max:500',
        ]);

        $account = StoreCreditAccount::firstOrCreate(['user_id' => $user->id]);

        if ($request->amount > 0) {
            $account->credit($request->amount, $request->reason);
        } else {
            $account->debit(abs($request->amount), $request->reason);
        }

        return response()->json(['data' => $account->fresh()]);
    }
}
