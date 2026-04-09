<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreLocation extends Model
{
    protected $fillable = [
        'name', 'address', 'city', 'postal_code', 'country',
        'phone', 'email', 'latitude', 'longitude',
        'working_hours', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'working_hours' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Pronalazi lokacije unutar datog radijusa (km) od koordinata.
     * Koristi Haversine formulu.
     */
    public function scopeNearby($query, float $lat, float $lng, float $radiusKm = 50)
    {
        return $query->selectRaw('*, (
            6371 * acos(
                cos(radians(?)) * cos(radians(latitude)) *
                cos(radians(longitude) - radians(?)) +
                sin(radians(?)) * sin(radians(latitude))
            )
        ) AS distance', [$lat, $lng, $lat])
            ->having('distance', '<=', $radiusKm)
            ->orderBy('distance');
    }
}
