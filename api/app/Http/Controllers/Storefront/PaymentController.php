<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    public function methods(): JsonResponse
    {
        $methods = PaymentMethod::where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'code', 'name', 'description', 'instructions', 'additional_cost']);

        return response()->json(['data' => $methods]);
    }
}
