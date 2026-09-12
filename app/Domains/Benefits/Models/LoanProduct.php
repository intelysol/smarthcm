<?php

namespace App\Domains\Benefits\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoanProduct extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'loan_products';

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'loan_type',
        'currency',
        'minimum_amount',
        'maximum_amount',
        'max_installments',
        'interest_rate_annual',
        'interest_method',
        'min_service_months',
        'max_salary_multiple',
        'max_monthly_deduction_ratio',
        'deduction_priority',
        'requires_guarantor',
        'is_active',
        'version',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'minimum_amount' => 'decimal:4',
        'maximum_amount' => 'decimal:4',
        'max_installments' => 'integer',
        'interest_rate_annual' => 'decimal:4',
        'min_service_months' => 'integer',
        'max_salary_multiple' => 'decimal:2',
        'max_monthly_deduction_ratio' => 'decimal:2',
        'deduction_priority' => 'integer',
        'requires_guarantor' => 'boolean',
        'is_active' => 'boolean',
        'version' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(LoanProductVersion::class);
    }

    public function eligibilityRules(): HasMany
    {
        return $this->hasMany(LoanEligibilityRule::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(LoanApplication::class);
    }
}
