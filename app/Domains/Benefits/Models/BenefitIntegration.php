<?php

namespace App\Domains\Benefits\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BenefitIntegration extends Model
{
    use HasUuids;

    protected $table = 'benefit_integrations';

    protected $fillable = [
        'tenant_id',
        'integration_type',
        'target_name',
        'reference_type',
        'reference_id',
        'direction',
        'status',
        'payload',
        'response_payload',
        'error_message',
        'processed_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'response_payload' => 'array',
        'processed_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
