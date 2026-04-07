<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxRate extends Model
{
    protected $fillable = ['name', 'rate', 'country', 'is_default', 'is_active'];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:2',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public static function getForCountry(string $country = 'RS'): ?self
    {
        return self::where('country', $country)
            ->where('is_active', true)
            ->first()
            ?? self::where('is_default', true)
                ->where('is_active', true)
                ->first();
    }

    public function calculateTax(float $amount): float
    {
        return round($amount * ((float) $this->rate / 100), 2);
    }
}
