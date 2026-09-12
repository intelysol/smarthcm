<?php

namespace App\Domains\Benefits\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BenefitBeneficiary extends Model
{
    use HasUuids;

    protected $table = 'benefit_beneficiaries';

    protected $fillable = [
        'tenant_id',
        'benefit_enrollment_id',
        'employee_id',
        'plan_type',
        'name',
        'relationship',
        'percentage_allocation',
        'contact_phone',
        'contact_email',
        'effective_from',
        'effective_to',
        'is_primary',
        'is_contingent',
    ];

    protected $casts = [
        'percentage_allocation' => 'decimal:2',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'is_primary' => 'boolean',
        'is_contingent' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(BenefitEnrollment::class, 'benefit_enrollment_id');
    }
}
