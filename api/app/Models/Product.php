<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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

    // --- Scopes ---

    public function scopeOnSale(Builder $query): Builder
    {
        return $query->whereNotNull('sale_price')
            ->where(function ($q) {
                $q->where(function ($q2) {
                    $q2->whereNull('sale_price_from')->whereNull('sale_price_to');
                })->orWhere(function ($q2) {
                    $q2->where('sale_price_from', '<=', now())
                       ->where('sale_price_to', '>=', now());
                });
            });
    }

    // --- Accessors ---

    public function getEffectivePriceAttribute(): string
    {
        if ($this->isSaleActive()) {
            return $this->sale_price;
        }

        return $this->price;
    }

    public function getSalePercentageAttribute(): ?int
    {
        if (! $this->isSaleActive() || $this->price <= 0) {
            return null;
        }

        return (int) round((1 - $this->sale_price / $this->price) * 100);
    }

    public function getFormattedUnitPriceAttribute(): ?string
    {
        if (! $this->unit_price || ! $this->unit_label) {
            return null;
        }

        return number_format($this->unit_price, 2, ',', '.') . ' RSD/' . $this->unit_label;
    }

    // --- Helpers ---

    public function isSaleActive(): bool
    {
        if (! $this->sale_price) {
            return false;
        }

        if (! $this->sale_price_from && ! $this->sale_price_to) {
            return true;
        }

        $now = now();

        return (! $this->sale_price_from || $this->sale_price_from <= $now)
            && (! $this->sale_price_to || $this->sale_price_to >= $now);
    }

    // --- Relations ---

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function approvedReviews(): HasMany
    {
        return $this->hasMany(Review::class)->where('status', 'approved');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class)->orderByDesc('created_at');
    }

    public function relatedProducts(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'product_relations', 'product_id', 'related_product_id')
            ->wherePivot('type', 'related')
            ->orderBy('product_relations.sort_order');
    }

    public function crossSellProducts(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'product_relations', 'product_id', 'related_product_id')
            ->wherePivot('type', 'cross_sell')
            ->orderBy('product_relations.sort_order');
    }

    public function upSellProducts(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'product_relations', 'product_id', 'related_product_id')
            ->wherePivot('type', 'up_sell')
            ->orderBy('product_relations.sort_order');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }
}
