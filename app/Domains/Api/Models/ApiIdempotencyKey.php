<?php

namespace App\Domains\Api\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ApiIdempotencyKey extends Model
{
    use HasUuids;

    protected $table = 'api_idempotency_keys';

    protected $fillable = [
        'tenant_id',
        'key',
        'method',
        'path',
        'response_status',
        'response_body',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'response_status' => 'integer',
            'response_body' => 'array',
            'expires_at' => 'datetime',
        ];
    }
}
