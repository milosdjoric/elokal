<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'short_description',
        'description',
        'price',
        'sale_price',
        'sale_price_from',
        'sale_price_to',
        'cost_price',
        'unit_price',
        'unit_label',
        'sku',
        'stock_quantity',
        'is_active',
        'featured',
        'sort_order',
        'meta_title',
        'meta_description',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'sale_price_from' => 'datetime',
            'sale_price_to' => 'datetime',
            'is_active' => 'boolean',
            'featured' => 'boolean',
        ];
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }
}
