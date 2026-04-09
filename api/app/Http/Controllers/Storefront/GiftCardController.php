<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\GiftCard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GiftCardController extends Controller
{
    public function checkByCode(string $code): JsonResponse
    {
        $card = GiftCard::where('code', strtoupper(str_replace(' ', '', $code)))->first();

        if (! $card) {
            return response()->json(['message' => 'Poklon kartica ne postoji.'], 404);
        }

        $usable = $card->isUsable();
        if ($usable !== true) {
            return response()->json(['message' => $usable], 422);
        }

        return response()->json([
            'data' => [
                'code' => $card->code,
                'balance' => $card->balance,
            ],
        ]);
    }

    public function purchase(Request $request): JsonResponse
    {
        if (! feature('feature_gift_cards')) {
            abort(404);
        }

        $request->validate([
            'amount' => 'required|numeric|min:500|max:50000',
            'recipient_email' => 'required|email',
            'recipient_name' => 'required|string|max:255',
            'message' => 'nullable|string|max:1000',
        ]);

        $card = GiftCard::create([
            'code' => GiftCard::generateCode(),
            'initial_amount' => $request->amount,
            'balance' => $request->amount,
            'purchased_by' => $request->user()?->id,
            'recipient_email' => $request->recipient_email,
            'recipient_name' => $request->recipient_name,
            'message' => $request->message,
            'is_active' => true,
            'expires_at' => now()->addYear(),
        ]);

        $card->transactions()->create([
            'amount' => $request->amount,
            'type' => 'purchase',
            'balance_after' => $card->balance,
            'note' => "Kupovina poklon kartice za {$request->recipient_name}",
        ]);

        // TODO: Email primaocu sa kodom kad bude SMTP konfigurisan
        // Mail::to($request->recipient_email)->send(new GiftCardReceivedMail($card));

        return response()->json([
            'data' => [
                'code' => $card->code,
                'amount' => $card->initial_amount,
                'recipient_email' => $card->recipient_email,
                'recipient_name' => $card->recipient_name,
                'expires_at' => $card->expires_at,
            ],
            'message' => 'Poklon kartica je uspešno kupljena.',
        ], 201);
    }

    public function check(Request $request): JsonResponse
    {
        $request->validate(['code' => 'required|string']);

        $card = GiftCard::where('code', strtoupper(str_replace(' ', '', $request->code)))->first();

        if (! $card) {
            return response()->json(['message' => 'Poklon kartica ne postoji.'], 404);
        }

        $usable = $card->isUsable();
        if ($usable !== true) {
            return response()->json(['message' => $usable], 422);
        }

        return response()->json([
            'data' => [
                'code' => $card->code,
                'balance' => $card->balance,
            ],
        ]);
    }
}
