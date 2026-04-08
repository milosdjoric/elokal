<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => Currency::orderBy('code')->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code' => 'required|string|size:3|unique:currencies',
            'name' => 'required|string|max:100',
            'symbol' => 'required|string|max:5',
            'exchange_rate' => 'required|numeric|min:0.000001',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'decimal_places' => 'integer|min:0|max:4',
        ]);

        $data['code'] = strtoupper($data['code']);

        if (! empty($data['is_default'])) {
            Currency::where('is_default', true)->update(['is_default' => false]);
        }

        return response()->json(['data' => Currency::create($data)], 201);
    }

    public function update(Request $request, Currency $currency): JsonResponse
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:100',
            'symbol' => 'sometimes|string|max:5',
            'exchange_rate' => 'sometimes|numeric|min:0.000001',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'decimal_places' => 'integer|min:0|max:4',
        ]);

        if (! empty($data['is_default'])) {
            Currency::where('id', '!=', $currency->id)->where('is_default', true)->update(['is_default' => false]);
        }

        $currency->update($data);
        return response()->json(['data' => $currency]);
    }

    public function destroy(Currency $currency): JsonResponse
    {
        $currency->delete();
        return response()->json(['message' => 'Valuta obrisana.']);
    }
}
