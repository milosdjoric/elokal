<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Carrier extends Model
{
    protected $fillable = ['code', 'name', 'tracking_url_pattern', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function getTrackingUrl(string $trackingNumber): ?string
    {
        if (! $this->tracking_url_pattern) {
            return null;
        }

        return str_replace('{tracking_number}', $trackingNumber, $this->tracking_url_pattern);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
