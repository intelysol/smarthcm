<?php

namespace App\Domains\Api\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiBulkJob extends Model
{
    use HasUuids;

    protected $table = 'api_bulk_jobs';

    protected $fillable = [
        'tenant_id',
        'api_client_id',
        'operation',
        'resource',
        'status',
        'payload',
        'result',
        'download_document_id',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'result' => 'array',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(ApiClient::class, 'api_client_id');
    }
}
