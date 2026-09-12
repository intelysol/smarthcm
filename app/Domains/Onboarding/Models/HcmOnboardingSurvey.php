<?php

namespace App\Domains\Onboarding\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmOnboardingSurvey extends Model
{
    use HasUuids;

    protected $table = 'hcm_onboarding_surveys';

    protected $fillable = [
        'tenant_id',
        'case_id',
        'employee_id',
        'experience_rating',
        'it_readiness_rating',
        'manager_support_rating',
        'feedback_comments',
        'submitted_at',
    ];

    protected $casts = [
        'experience_rating' => 'integer',
        'it_readiness_rating' => 'integer',
        'manager_support_rating' => 'integer',
        'submitted_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function case(): BelongsTo
    {
        return $this->belongsTo(HcmOnboardingCase::class, 'case_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
