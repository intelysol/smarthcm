<?php

namespace App\Domains\Attendance\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmPayrollTimeReconciliation extends Model
{
    use HasUuids;

    protected $table = 'hcm_payroll_time_reconciliations';

    protected $fillable = [
        'tenant_id',
        'payroll_time_export_id',
        'payroll_batch_id',
        'status',
        'total_records_checked',
        'discrepant_records_count',
        'discrepancies',
        'reconciled_by',
        'reconciled_at',
    ];

    protected $casts = [
        'total_records_checked' => 'integer',
        'discrepant_records_count' => 'integer',
        'discrepancies' => 'array',
        'reconciled_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function export(): BelongsTo
    {
        return $this->belongsTo(HcmPayrollTimeExport::class, 'payroll_time_export_id');
    }

    public function reconciler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reconciled_by');
    }
}