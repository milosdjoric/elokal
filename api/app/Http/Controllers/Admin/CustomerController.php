<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::withCount('orders')
            ->withSum('orders', 'total')
            ->orderByDesc('created_at');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $perPage = min($request->input('per_page', 15), 100);
        $customers = $query->paginate($perPage);

        return response()->json([
            'data' => $customers->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'orders_count' => $user->orders_count,
                'total_spent' => $user->orders_sum_total ?? '0.00',
                'created_at' => $user->created_at,
            ]),
            'meta' => [
                'current_page' => $customers->currentPage(),
                'last_page' => $customers->lastPage(),
                'per_page' => $customers->perPage(),
                'total' => $customers->total(),
            ],
        ]);
    }

    public function show(User $customer): JsonResponse
    {
        $customer->load(['addresses', 'orders.items']);
        $customer->loadCount('orders');
        $customer->loadSum('orders', 'total');

        return response()->json([
            'data' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'newsletter_subscribed' => $customer->newsletter_subscribed,
                'orders_count' => $customer->orders_count,
                'total_spent' => $customer->orders_sum_total ?? '0.00',
                'created_at' => $customer->created_at,
                'addresses' => $customer->addresses,
                'orders' => OrderResource::collection($customer->orders),
            ],
        ]);
    }
}
