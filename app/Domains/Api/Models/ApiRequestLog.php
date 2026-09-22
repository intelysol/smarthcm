<?php

namespace App\Domains\Api\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiRequestLog extends Model
{
    public $timestamps = false;

    protected $table = 'api_request_logs';

    protected $fillable = [
        'tenant_id',
        'api_client_id',
        'api_endpoint_id',
        'correlation_id',
        'response_status',
        'latency_ms',
        'ip_address',
        'requested_at',
    ];

    protected function casts(): array
    {
        return [
            'response_status' => 'integer',
            'latency_ms' => 'integer',
            'requested_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(ApiClient::class, 'api_client_id');
    }

    public function endpoint(): BelongsTo
    {
        return $this->belongsTo(ApiEndpoint::class, 'api_endpoint_id');
    }
}
