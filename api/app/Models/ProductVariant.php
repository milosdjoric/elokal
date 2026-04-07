<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'sku', 'price', 'sale_price', 'weight',
        'stock_quantity', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'weight' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(AttributeValue::class, 'product_variant_attribute_value');
    }

    public function images(): BelongsToMany
    {
        return $this->belongsToMany(ProductImage::class, 'product_variant_image');
    }

    public function getEffectivePriceAttribute(): string
    {
        if ($this->sale_price && $this->sale_price > 0) {
            return $this->sale_price;
        }
        if ($this->price && $this->price > 0) {
            return $this->price;
        }
        return $this->product->effective_price;
    }
}
