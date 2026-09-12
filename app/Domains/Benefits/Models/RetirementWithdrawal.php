<?php

namespace App\Domains\Benefits\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RetirementWithdrawal extends Model
{
    use HasUuids;

    protected $table = 'retirement_withdrawals';

    protected $fillable = [
        'tenant_id',
        'retirement_account_id',
        'employee_id',
        'withdrawal_type',
        'requested_amount',
        'approved_amount',
        'tax_withheld',
        'net_disbursed_amount',
        'currency',
        'status',
        'reason',
        'workflow_instance_id',
        'approved_by',
        'approved_at',
        'disbursed_at',
    ];

    protected $casts = [
        'requested_amount' => 'decimal:4',
        'approved_amount' => 'decimal:4',
        'tax_withheld' => 'decimal:4',
        'net_disbursed_amount' => 'decimal:4',
        'approved_at' => 'datetime',
        'disbursed_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(RetirementAccount::class, 'retirement_account_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
