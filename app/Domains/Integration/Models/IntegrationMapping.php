<?php

namespace App\Domains\Integration\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IntegrationMapping extends Model
{
    use HasUuids;

    protected $table = 'integration_mappings';

    protected $fillable = [
        'connection_id',
        'name',
        'source_format',
        'target_format',
        'mapping',
        'validation_rules',
    ];

    protected function casts(): array
    {
        return [
            'mapping' => 'array',
            'validation_rules' => 'array',
        ];
    }

    public function connection(): BelongsTo
    {
        return $this->belongsTo(IntegrationConnection::class, 'connection_id');
    }
}
