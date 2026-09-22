<?php

namespace App\Domains\Integration\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IntegrationSyncRun extends Model
{
    use HasUuids;

    protected $table = 'integration_sync_runs';

    protected $fillable = [
        'connection_id',
        'sync_type',
        'status',
        'processed',
        'failed',
        'checkpoint',
        'error_message',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'checkpoint' => 'array',
            'processed' => 'integer',
            'failed' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function connection(): BelongsTo
    {
        return $this->belongsTo(IntegrationConnection::class, 'connection_id');
    }
}
