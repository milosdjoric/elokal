<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = ['code', 'name', 'symbol', 'exchange_rate', 'is_default', 'is_active', 'decimal_places'];

    protected function casts(): array
    {
        return [
            'exchange_rate' => 'decimal:6',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function convert(float $amount): float
    {
        return round($amount * (float) $this->exchange_rate, $this->decimal_places);
    }

    public static function getDefault(): ?self
    {
        return self::where('is_default', true)->where('is_active', true)->first();
    }
}
