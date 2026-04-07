<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Webhook extends Model
{
    protected $fillable = ['url', 'events', 'secret', 'is_active', 'failures', 'last_triggered_at'];

    protected function casts(): array
    {
        return [
            'events' => 'array',
            'is_active' => 'boolean',
            'last_triggered_at' => 'datetime',
        ];
    }

    public function logs(): HasMany
    {
        return $this->hasMany(WebhookLog::class)->orderByDesc('created_at');
    }

    public static function generateSecret(): string
    {
        return 'whsec_' . Str::random(32);
    }

    public function shouldReceive(string $event): bool
    {
        return $this->is_active && in_array($event, $this->events ?? []);
    }
}
