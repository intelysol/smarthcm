<?php

namespace App\Domains\WorkforceGovernance\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HcmGovReconciliation extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'hcm_gov_reconciliations';

    protected $fillable = [
        'tenant_id',
        'reconciliation_code',
        'name',
        'source_domain',
        'target_domain',
        'comparison_entity',
        'source_record_count',
        'target_record_count',
        'matched_count',
        'unmatched_count',
        'conflict_count',
        'variance_amount',
        'status',
        'discrepancy_details',
        'reconciled_at',
    ];

    protected $casts = [
        'source_record_count' => 'integer',
        'target_record_count' => 'integer',
        'matched_count' => 'integer',
        'unmatched_count' => 'integer',
        'conflict_count' => 'integer',
        'variance_amount' => 'decimal:2',
        'discrepancy_details' => 'array',
        'reconciled_at' => 'datetime',
    ];
}
