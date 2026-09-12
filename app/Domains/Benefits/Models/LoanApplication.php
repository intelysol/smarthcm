<?php

namespace App\Domains\Benefits\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoanApplication extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'loan_applications';

    protected $fillable = [
        'tenant_id',
        'application_number',
        'employee_id',
        'loan_product_id',
        'requested_amount',
        'requested_tenure_months',
        'approved_amount',
        'approved_tenure_months',
        'interest_rate',
        'interest_method',
        'currency',
        'purpose',
        'status',
        'workflow_instance_id',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'created_by',
    ];

    protected $casts = [
        'requested_amount' => 'decimal:4',
        'requested_tenure_months' => 'integer',
        'approved_amount' => 'decimal:4',
        'approved_tenure_months' => 'integer',
        'interest_rate' => 'decimal:4',
        'approved_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(LoanProduct::class, 'loan_product_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function agreement(): HasOne
    {
        return $this->hasOne(LoanAgreement::class);
    }

    public function disbursements(): HasMany
    {
        return $this->hasMany(LoanDisbursement::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(LoanSchedule::class);
    }

    public function activeSchedule(): HasOne
    {
        return $this->hasOne(LoanSchedule::class)->where('is_active', true);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(LoanTransaction::class)->orderBy('transaction_date');
    }

    public function restructures(): HasMany
    {
        return $this->hasMany(LoanRestructure::class);
    }

    public function settlements(): HasMany
    {
        return $this->hasMany(LoanSettlement::class);
    }
}
