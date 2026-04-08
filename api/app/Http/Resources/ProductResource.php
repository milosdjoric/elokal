<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'price' => $this->price,
            'sale_price' => $this->sale_price,
            'sale_price_from' => $this->sale_price_from,
            'sale_price_to' => $this->sale_price_to,
            'cost_price' => $this->cost_price,
            'unit_price' => $this->unit_price,
            'unit_label' => $this->unit_label,
            'sku' => $this->sku,
            'stock_quantity' => $this->stock_quantity,
            'is_active' => $this->is_active,
            'featured' => $this->featured,
            'sort_order' => $this->sort_order,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'effective_price' => $this->effective_price,
            'sale_percentage' => $this->sale_percentage,
            'formatted_unit_price' => $this->formatted_unit_price,
            'is_on_sale' => $this->isSaleActive(),
            'times_sold' => $this->times_sold,
            'categories' => $this->whenLoaded('categories', fn () => $this->categories->pluck('id')),
            'images' => $this->whenLoaded('images'),
            'variants' => ProductVariantResource::collection($this->whenLoaded('variants')),
            'related_products' => self::collection($this->whenLoaded('relatedProducts')),
            'cross_sell_products' => self::collection($this->whenLoaded('crossSellProducts')),
            'up_sell_products' => self::collection($this->whenLoaded('upSellProducts')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
