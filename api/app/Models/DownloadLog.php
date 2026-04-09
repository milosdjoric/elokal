<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class DownloadLog extends Model
{
    protected $fillable = [
        'downloadable_file_id', 'order_id', 'user_id',
        'download_count', 'expires_at', 'token',
    ];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime'];
    }

    public static function generateToken(): string
    {
        return Str::random(64);
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(DownloadableFile::class, 'downloadable_file_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function canDownload(): bool|string
    {
        if ($this->expires_at && $this->expires_at->isPast()) {
            return 'Link za preuzimanje je istekao.';
        }

        $maxDownloads = $this->file?->max_downloads;
        if ($maxDownloads && $this->download_count >= $maxDownloads) {
            return 'Dostignut je maksimalan broj preuzimanja.';
        }

        return true;
    }
}
