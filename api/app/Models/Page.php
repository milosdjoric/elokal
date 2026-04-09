<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use \App\Traits\HasTranslations;

    public array $translatableFields = ['title', 'slug', 'content', 'meta_title', 'meta_description'];
    protected $fillable = ['title', 'slug', 'content', 'meta_title', 'meta_description', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
