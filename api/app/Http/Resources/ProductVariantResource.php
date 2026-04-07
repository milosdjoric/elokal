<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'price' => $this->price,
            'sale_price' => $this->sale_price,
            'effective_price' => $this->effective_price,
            'weight' => $this->weight,
            'stock_quantity' => $this->stock_quantity,
            'is_active' => $this->is_active,
            'attributes' => $this->whenLoaded('attributeValues', fn () =>
                $this->attributeValues->map(fn ($av) => [
                    'attribute_id' => $av->attribute_id,
                    'attribute_name' => $av->attribute->name,
                    'attribute_slug' => $av->attribute->slug,
                    'attribute_type' => $av->attribute->type,
                    'value_id' => $av->id,
                    'value' => $av->value,
                    'color_hex' => $av->color_hex,
                    'image_path' => $av->image_path,
                ])
            ),
            'images' => $this->whenLoaded('images'),
        ];
    }
}
