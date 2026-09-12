<?php

namespace App\Domains\Compensation\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompensationPayrollExport extends CompensationModel
{
    protected $fillable = [
        'tenant_id',
        'compensation_cycle_id',
        'batch_reference',
        'effective_date',
        'record_count',
        'total_increase_payroll_impact',
        'currency',
        'status',
        'sync_payload',
        'integration_response',
        'exported_by',
        'exported_at',
    ];

    protected function casts(): array
    {
        return [
            'effective_date' => 'date',
            'record_count' => 'integer',
            'total_increase_payroll_impact' => 'decimal:2',
            'exported_at' => 'datetime',
        ];
    }

    public function cycle(): BelongsTo
    {
        return $this->belongsTo(CompensationCycle::class, 'compensation_cycle_id');
    }

    public function exportedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'exported_by');
    }
}
