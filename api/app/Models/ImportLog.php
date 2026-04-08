<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportLog extends Model
{
    protected $fillable = [
        'admin_id', 'type', 'filename', 'rows_total',
        'rows_created', 'rows_updated', 'rows_failed',
        'errors', 'status',
    ];

    protected function casts(): array
    {
        return ['errors' => 'array'];
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }
}
