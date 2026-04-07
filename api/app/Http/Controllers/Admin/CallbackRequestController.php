<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CallbackRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CallbackRequestController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = CallbackRequest::with('product')->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = min($request->input('per_page', 15), 100);

        return response()->json($query->paginate($perPage));
    }

    public function updateStatus(Request $request, CallbackRequest $callbackRequest): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:' . implode(',', CallbackRequest::STATUSES),
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $callbackRequest->update($request->only(['status', 'admin_notes']));

        return response()->json(['data' => $callbackRequest->load('product')]);
    }
}
