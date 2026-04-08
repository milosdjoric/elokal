<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Look extends Model
{
    protected $fillable = ['title', 'image_path', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function hotspots(): HasMany
    {
        return $this->hasMany(LookHotspot::class);
    }
}
