<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product' => $this->when($this->relationLoaded('product'), fn () => [
                'id' => $this->product->id,
                'name' => $this->product->name,
                'slug' => $this->product->slug,
            ]),
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ],
            'rating' => $this->rating,
            'title' => $this->title,
            'content' => $this->content,
            'is_verified_purchase' => $this->is_verified_purchase,
            'status' => $this->when($request->is('api/admin/*'), $this->status),
            'admin_reply' => $this->admin_reply,
            'admin_replied_at' => $this->admin_replied_at,
            'helpful_count' => $this->helpful()->where('helpful', true)->count(),
            'not_helpful_count' => $this->helpful()->where('helpful', false)->count(),
            'created_at' => $this->created_at,
        ];
    }
}
