<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TaxRate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaxRateController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => TaxRate::all()]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'rate' => 'required|numeric|min:0|max:100',
            'country' => 'string|max:2',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ]);

        if (! empty($data['is_default'])) {
            TaxRate::where('is_default', true)->update(['is_default' => false]);
        }

        return response()->json(['data' => TaxRate::create($data)], 201);
    }

    public function update(Request $request, TaxRate $taxRate): JsonResponse
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'rate' => 'sometimes|numeric|min:0|max:100',
            'country' => 'string|max:2',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ]);

        if (! empty($data['is_default'])) {
            TaxRate::where('id', '!=', $taxRate->id)->where('is_default', true)->update(['is_default' => false]);
        }

        $taxRate->update($data);

        return response()->json(['data' => $taxRate]);
    }

    public function destroy(TaxRate $taxRate): JsonResponse
    {
        $taxRate->delete();

        return response()->json(['message' => 'Stopa obrisana.']);
    }
}
