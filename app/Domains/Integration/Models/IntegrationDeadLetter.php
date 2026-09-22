<?php

namespace App\Domains\Integration\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IntegrationDeadLetter extends Model
{
    use HasUuids;

    protected $table = 'integration_dead_letters';

    protected $fillable = [
        'connection_id',
        'job_type',
        'payload',
        'error_message',
        'attempts',
        'failed_at',
        'replayed_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'attempts' => 'integer',
            'failed_at' => 'datetime',
            'replayed_at' => 'datetime',
        ];
    }

    public function connection(): BelongsTo
    {
        return $this->belongsTo(IntegrationConnection::class, 'connection_id');
    }
}
