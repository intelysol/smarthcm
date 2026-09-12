<?php

namespace App\Domains\HealthSafety\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class HcmMedicalAssessment extends Model
{
    use HasUuids;

    protected $table = 'hcm_medical_assessments';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'health_requirement_id',
        'medical_provider_id',
        'assessment_type',
        'requested_date',
        'scheduled_date',
        'completed_date',
        'status',
        'scheduled_by',
        'document_id',
        'operational_notes',
    ];

    protected $casts = [
        'requested_date' => 'date',
        'scheduled_date' => 'date',
        'completed_date' => 'date',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function requirement(): BelongsTo
    {
        return $this->belongsTo(HcmHealthRequirement::class, 'health_requirement_id');
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(HcmMedicalProvider::class, 'medical_provider_id');
    }

    public function scheduledByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scheduled_by');
    }

    public function fitnessRecord(): HasOne
    {
        return $this->hasOne(HcmMedicalFitnessRecord::class, 'assessment_id');
    }

    public function getFitnessOutcomeAttribute(): ?string
    {
        return $this->fitnessRecord?->fitness_status;
    }
}
