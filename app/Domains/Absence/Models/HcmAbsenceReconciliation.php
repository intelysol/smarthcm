<?php

namespace App\Domains\Absence\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmAbsenceReconciliation extends Model
{
    use HasUuids;

    protected $table = 'hcm_absence_reconciliations';

    protected $fillable = [
        'tenant_id',
        'reconciliation_date',
        'period_start',
        'period_end',
        'status',
        'total_records_checked',
        'discrepant_records_count',
        'discrepancies',
        'reconciled_by',
        'reconciled_at',
    ];

    protected $casts = [
        'reconciliation_date' => 'date',
        'period_start' => 'date',
        'period_end' => 'date',
        'total_records_checked' => 'integer',
        'discrepant_records_count' => 'integer',
        'discrepancies' => 'array',
        'reconciled_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function reconciler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reconciled_by');
    }
}