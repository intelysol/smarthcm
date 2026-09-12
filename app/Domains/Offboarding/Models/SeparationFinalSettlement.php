<?php

namespace App\Domains\Offboarding\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeparationFinalSettlement extends Model
{
    use HasUuids;

    protected $table = 'separation_final_settlements';

    protected $fillable = [
        'tenant_id',
        'separation_request_id',
        'payroll_run_id',
        'gross_payable',
        'deductions',
        'net_payable',
        'currency',
        'settlement_status',
        'approved_at',
        'approved_by',
        'payment_date',
        'snapshot_data',
    ];

    protected $casts = [
        'gross_payable' => 'decimal:2',
        'deductions' => 'decimal:2',
        'net_payable' => 'decimal:2',
        'approved_at' => 'datetime',
        'payment_date' => 'date',
        'snapshot_data' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(SeparationRequest::class, 'separation_request_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
