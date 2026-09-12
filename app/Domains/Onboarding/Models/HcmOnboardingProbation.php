<?php

namespace App\Domains\Onboarding\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HcmOnboardingProbation extends Model
{
    use HasUuids;

    protected $table = 'hcm_onboarding_probations';

    protected $fillable = [
        'tenant_id',
        'case_id',
        'employee_id',
        'probation_start_date',
        'probation_end_date',
        'extended_to_date',
        'status',
        'outcome',
    ];

    protected $casts = [
        'probation_start_date' => 'date',
        'probation_end_date' => 'date',
        'extended_to_date' => 'date',
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

    public function reviews(): HasMany
    {
        return $this->hasMany(HcmOnboardingProbationReview::class, 'probation_id');
    }
}
