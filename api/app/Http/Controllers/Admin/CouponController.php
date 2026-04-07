<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CouponController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Coupon::orderByDesc('created_at');

        if ($request->filled('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        if ($request->filled('search')) {
            $query->where('code', 'like', "%{$request->search}%");
        }

        return response()->json($query->paginate(15));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code' => 'required|string|max:50|unique:coupons',
            'type' => 'required|in:' . implode(',', Coupon::TYPES),
            'value' => 'required|numeric|min:0.01',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'max_uses_per_user' => 'integer|min:1',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:starts_at',
            'is_active' => 'boolean',
            'description' => 'nullable|string|max:500',
        ]);

        $data['code'] = strtoupper($data['code']);
        $coupon = Coupon::create($data);

        return response()->json(['data' => $coupon], 201);
    }

    public function show(Coupon $coupon): JsonResponse
    {
        $coupon->loadCount('usages');

        return response()->json(['data' => $coupon]);
    }

    public function update(Request $request, Coupon $coupon): JsonResponse
    {
        $data = $request->validate([
            'code' => "sometimes|string|max:50|unique:coupons,code,{$coupon->id}",
            'type' => 'sometimes|in:' . implode(',', Coupon::TYPES),
            'value' => 'sometimes|numeric|min:0.01',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'max_uses_per_user' => 'integer|min:1',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date',
            'is_active' => 'boolean',
            'description' => 'nullable|string|max:500',
        ]);

        if (isset($data['code'])) {
            $data['code'] = strtoupper($data['code']);
        }

        $coupon->update($data);

        return response()->json(['data' => $coupon]);
    }

    public function destroy(Coupon $coupon): JsonResponse
    {
        $coupon->delete();

        return response()->json(['message' => 'Kupon obrisan.']);
    }

    public function bulkGenerate(Request $request): JsonResponse
    {
        $request->validate([
            'prefix' => 'required|string|max:10',
            'count' => 'required|integer|min:1|max:500',
            'type' => 'required|in:' . implode(',', Coupon::TYPES),
            'value' => 'required|numeric|min:0.01',
            'max_uses' => 'nullable|integer|min:1',
            'max_uses_per_user' => 'integer|min:1',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date',
        ]);

        $coupons = [];
        for ($i = 0; $i < $request->count; $i++) {
            $code = strtoupper($request->prefix . '-' . Str::random(6));
            $coupons[] = Coupon::create([
                'code' => $code,
                'type' => $request->type,
                'value' => $request->value,
                'max_uses' => $request->max_uses ?? 1,
                'max_uses_per_user' => $request->max_uses_per_user ?? 1,
                'starts_at' => $request->starts_at,
                'expires_at' => $request->expires_at,
                'is_active' => true,
            ]);
        }

        return response()->json([
            'message' => "{$request->count} kupona generisano.",
            'count' => count($coupons),
        ], 201);
    }

    public function stats(Coupon $coupon): JsonResponse
    {
        return response()->json([
            'times_used' => $coupon->times_used,
            'total_discount' => $coupon->usages()->sum('discount_amount'),
            'unique_users' => $coupon->usages()->distinct('user_id')->count('user_id'),
        ]);
    }
}
