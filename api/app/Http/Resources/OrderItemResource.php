<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product_name' => $this->product_name,
            'product_sku' => $this->product_sku,
            'product_slug' => $this->product?->slug,
            'product_image' => $this->product?->images()->where('is_primary', true)->first()?->image_path
                ?? $this->product?->images()->first()?->image_path,
            'price' => $this->price,
            'quantity' => $this->quantity,
            'line_total' => $this->line_total,
        ];
    }
}
