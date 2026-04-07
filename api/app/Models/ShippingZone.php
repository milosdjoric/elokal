<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingZone extends Model
{
    protected $fillable = ['name', 'countries', 'postal_codes', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return [
            'countries' => 'array',
            'postal_codes' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function methods(): HasMany
    {
        return $this->hasMany(ShippingMethod::class)->orderBy('sort_order');
    }

    public static function findForAddress(string $country, ?string $postalCode = null): ?self
    {
        return self::where('is_active', true)
            ->get()
            ->first(function ($zone) use ($country, $postalCode) {
                if (! in_array($country, $zone->countries)) {
                    return false;
                }
                if ($zone->postal_codes && $postalCode) {
                    return in_array($postalCode, $zone->postal_codes);
                }
                return true;
            });
    }
}
