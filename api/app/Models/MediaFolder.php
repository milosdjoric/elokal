<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MediaFolder extends Model
{
    protected $fillable = ['name', 'parent_id'];

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class, 'folder_id');
    }
}
