<?php

namespace App\Domains\Onboarding\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmOnboardingFormSubmission extends Model
{
    use HasUuids;

    protected $table = 'hcm_onboarding_form_submissions';

    protected $fillable = [
        'tenant_id',
        'case_id',
        'form_id',
        'form_data',
        'submitted_at',
    ];

    protected $casts = [
        'form_data' => 'array',
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

    public function form(): BelongsTo
    {
        return $this->belongsTo(HcmOnboardingForm::class, 'form_id');
    }
}
