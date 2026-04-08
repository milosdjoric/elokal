<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LookHotspot extends Model
{
    protected $fillable = ['look_id', 'product_id', 'x_position', 'y_position', 'label'];

    protected function casts(): array
    {
        return [
            'x_position' => 'decimal:2',
            'y_position' => 'decimal:2',
        ];
    }

    public function look(): BelongsTo
    {
        return $this->belongsTo(Look::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
