<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Review::with(['user', 'product', 'helpful'])->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        $perPage = min($request->input('per_page', 15), 100);

        return response()->json(
            ReviewResource::collection($query->paginate($perPage))
                ->response()
                ->getData(true)
        );
    }

    public function approve(Review $review): ReviewResource
    {
        $review->update(['status' => 'approved']);
        $review->load(['user', 'helpful']);
        return new ReviewResource($review);
    }

    public function reject(Review $review): ReviewResource
    {
        $review->update(['status' => 'rejected']);
        $review->load(['user', 'helpful']);
        return new ReviewResource($review);
    }

    public function reply(Request $request, Review $review): ReviewResource
    {
        $request->validate([
            'admin_reply' => 'required|string|max:2000',
        ]);

        $review->update([
            'admin_reply' => $request->admin_reply,
            'admin_replied_at' => now(),
        ]);

        $review->load(['user', 'helpful']);
        return new ReviewResource($review);
    }
}
