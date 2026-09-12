<?php

namespace App\Domains\Benefits\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class InsuranceClaim extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'benefit_claims';

    protected $fillable = [
        'tenant_id',
        'insurance_policy_id',
        'claim_number',
        'employee_id',
        'benefit_enrollment_id',
        'claim_type',
        'incident_date',
        'service_provider_name',
        'claimed_amount',
        'approved_amount',
        'currency',
        'is_sensitive_medical',
        'diagnosis_details',
        'status',
        'rejection_reason',
        'document_ids',
        'workflow_instance_id',
        'settled_at',
        'approved_by',
    ];

    protected $casts = [
        'incident_date' => 'date',
        'claimed_amount' => 'decimal:4',
        'approved_amount' => 'decimal:4',
        'is_sensitive_medical' => 'boolean',
        'document_ids' => 'array',
        'settled_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function policy(): BelongsTo
    {
        return $this->belongsTo(InsurancePolicy::class, 'insurance_policy_id');
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(BenefitEnrollment::class, 'benefit_enrollment_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(InsuranceClaimLine::class, 'benefit_claim_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(InsuranceClaimDocument::class, 'benefit_claim_id');
    }
}
