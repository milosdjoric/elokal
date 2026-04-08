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
