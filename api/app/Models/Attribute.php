<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attribute extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'type', 'is_filterable', 'is_visible_on_card', 'sort_order'];

    protected function casts(): array
    {
        return [
            'is_filterable' => 'boolean',
            'is_visible_on_card' => 'boolean',
        ];
    }

    public const TYPES = ['select', 'color', 'image'];

    public function values(): HasMany
    {
        return $this->hasMany(AttributeValue::class)->orderBy('sort_order');
    }
}
