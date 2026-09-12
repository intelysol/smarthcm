<?php

namespace App\Domains\Benefits\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RetirementAccount extends Model
{
    use HasUuids;

    protected $table = 'retirement_accounts';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'retirement_plan_id',
        'account_number',
        'currency',
        'opening_balance',
        'total_employee_contributions',
        'total_employer_contributions',
        'total_investment_returns',
        'total_withdrawals',
        'current_balance',
        'vested_balance',
        'status',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:4',
        'total_employee_contributions' => 'decimal:4',
        'total_employer_contributions' => 'decimal:4',
        'total_investment_returns' => 'decimal:4',
        'total_withdrawals' => 'decimal:4',
        'current_balance' => 'decimal:4',
        'vested_balance' => 'decimal:4',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(RetirementPlan::class, 'retirement_plan_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(RetirementTransaction::class)->orderBy('transaction_date');
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(RetirementWithdrawal::class);
    }
}
