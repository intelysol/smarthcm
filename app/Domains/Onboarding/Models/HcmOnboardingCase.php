<?php

namespace App\Domains\Onboarding\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Recruitment\Models\HcmRecruitmentApplication;
use App\Domains\Recruitment\Models\HcmRecruitmentOffer;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class HcmOnboardingCase extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'hcm_onboarding_cases';

    protected $fillable = [
        'tenant_id',
        'case_number',
        'employee_id',
        'recruitment_application_id',
        'offer_id',
        'template_version_id',
        'start_date',
        'target_completion_date',
        'status',
        'completion_percentage',
        'owner_id',
        'completed_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'target_completion_date' => 'date',
        'completion_percentage' => 'decimal:2',
        'completed_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(HcmRecruitmentApplication::class, 'recruitment_application_id');
    }

    public function offer(): BelongsTo
    {
        return $this->belongsTo(HcmRecruitmentOffer::class, 'offer_id');
    }

    public function templateVersion(): BelongsTo
    {
        return $this->belongsTo(HcmOnboardingTemplateVersion::class, 'template_version_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(HcmOnboardingCaseTask::class, 'case_id');
    }

    public function documentRequirements(): HasMany
    {
        return $this->hasMany(HcmOnboardingDocumentRequirement::class, 'case_id');
    }

    public function formSubmissions(): HasMany
    {
        return $this->hasMany(HcmOnboardingFormSubmission::class, 'case_id');
    }

    public function policyAcknowledgements(): HasMany
    {
        return $this->hasMany(HcmOnboardingPolicyAcknowledgement::class, 'case_id');
    }

    public function provisioningRequests(): HasMany
    {
        return $this->hasMany(HcmOnboardingProvisioningRequest::class, 'case_id');
    }

    public function buddyAssignment(): HasOne
    {
        return $this->hasOne(HcmOnboardingBuddyAssignment::class, 'case_id');
    }

    public function probation(): HasOne
    {
        return $this->hasOne(HcmOnboardingProbation::class, 'case_id');
    }

    public function survey(): HasOne
    {
        return $this->hasOne(HcmOnboardingSurvey::class, 'case_id');
    }
}
