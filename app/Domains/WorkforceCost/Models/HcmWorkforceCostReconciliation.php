<?php

namespace App\Domains\WorkforceCost\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmWorkforceCostReconciliation extends Model
{
    use HasUuids;

    protected $table = 'hcm_workforce_cost_reconciliations';

    protected $fillable = [
        'tenant_id',
        'reconciliation_type',
        'source_period',
        'source_total',
        'workforce_cost_total',
        'variance_amount',
        'currency',
        'status',
        'discrepancy_details',
        'reconciled_at',
    ];

    protected $casts = [
        'source_total' => 'decimal:4',
        'workforce_cost_total' => 'decimal:4',
        'variance_amount' => 'decimal:4',
        'discrepancy_details' => 'array',
        'reconciled_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}