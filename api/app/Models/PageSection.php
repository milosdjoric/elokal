<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageSection extends Model
{
    protected $fillable = ['page_key', 'type', 'data', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public const TYPES = [
        'hero_slideshow',
        'category_grid',
        'featured_products',
        'new_arrivals',
        'on_sale',
        'banner_full',
        'banner_split',
        'product_carousel',
        'text_block',
        'newsletter',
        'trust_badges',
        'blog_preview',
        'recently_viewed',
        'spacer',
    ];

    public function scopeForPage($query, string $pageKey)
    {
        return $query->where('page_key', $pageKey)
            ->where('is_active', true)
            ->orderBy('sort_order');
    }
}
