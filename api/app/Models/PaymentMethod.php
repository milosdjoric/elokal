<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = [
        'code', 'name', 'description', 'instructions',
        'additional_cost', 'is_active', 'is_online', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'additional_cost' => 'decimal:2',
            'is_active' => 'boolean',
            'is_online' => 'boolean',
        ];
    }
}
