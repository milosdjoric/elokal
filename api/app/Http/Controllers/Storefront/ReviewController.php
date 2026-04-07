<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReviewFormRequest;
use App\Http\Resources\ReviewResource;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Product $product, Request $request): JsonResponse
    {
        $query = $product->approvedReviews()->with('user', 'helpful');

        if ($request->filled('sort')) {
            match ($request->sort) {
                'newest' => $query->orderByDesc('created_at'),
                'oldest' => $query->orderBy('created_at'),
                'highest' => $query->orderByDesc('rating'),
                'lowest' => $query->orderBy('rating'),
                default => $query->orderByDesc('created_at'),
            };
        } else {
            $query->orderByDesc('created_at');
        }

        $reviews = $query->paginate(10);

        // Statistike
        $stats = [
            'average_rating' => round($product->approvedReviews()->avg('rating') ?? 0, 1),
            'total_reviews' => $product->approvedReviews()->count(),
            'distribution' => [],
        ];
        for ($i = 5; $i >= 1; $i--) {
            $stats['distribution'][$i] = $product->approvedReviews()->where('rating', $i)->count();
        }

        return response()->json(array_merge(
            ReviewResource::collection($reviews)->response()->getData(true),
            ['stats' => $stats],
        ));
    }

    public function store(ReviewFormRequest $request, Product $product): JsonResponse
    {
        // Proveri da li već postoji review
        $existing = Review::where('product_id', $product->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Već ste ostavili recenziju za ovaj proizvod.',
            ], 422);
        }

        // Proveri da li je verified purchase
        $isVerifiedPurchase = OrderItem::whereHas('order', function ($q) use ($request) {
            $q->where('user_id', $request->user()->id)
              ->whereIn('status', ['delivered', 'completed']);
        })->where('product_id', $product->id)->exists();

        $review = Review::create([
            'product_id' => $product->id,
            'user_id' => $request->user()->id,
            'rating' => $request->rating,
            'title' => $request->title,
            'content' => $request->content,
            'is_verified_purchase' => $isVerifiedPurchase,
            'status' => 'pending',
        ]);

        $review->load('user');

        return (new ReviewResource($review))
            ->response()
            ->setStatusCode(201);
    }

    public function helpful(Request $request, Review $review): JsonResponse
    {
        $request->validate([
            'helpful' => 'required|boolean',
        ]);

        $review->helpful()->updateOrCreate(
            ['user_id' => $request->user()->id],
            ['helpful' => $request->boolean('helpful')],
        );

        return response()->json([
            'helpful_count' => $review->helpful()->where('helpful', true)->count(),
            'not_helpful_count' => $review->helpful()->where('helpful', false)->count(),
        ]);
    }
}
