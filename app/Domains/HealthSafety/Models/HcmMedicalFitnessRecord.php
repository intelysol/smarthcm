<?php

namespace App\Domains\HealthSafety\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HcmMedicalFitnessRecord extends Model
{
    use HasUuids;

    protected $table = 'hcm_medical_fitness_records';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'assessment_id',
        'medical_provider_id',
        'fitness_status',
        'determined_date',
        'effective_from',
        'effective_to',
        'next_review_date',
        'certificate_reference',
        'document_id',
        'has_restrictions',
        'recorded_by',
        'summary_notes',
        'is_current',
    ];

    protected $casts = [
        'determined_date' => 'date',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'next_review_date' => 'date',
        'has_restrictions' => 'boolean',
        'is_current' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(HcmMedicalAssessment::class, 'assessment_id');
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(HcmMedicalProvider::class, 'medical_provider_id');
    }

    public function recordedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function restrictions(): HasMany
    {
        return $this->hasMany(HcmMedicalRestriction::class, 'fitness_record_id');
    }

    public function getStatusAttribute(): ?string
    {
        return $this->attributes['fitness_status'] ?? null;
    }

    public function setStatusAttribute(?string $value): void
    {
        $this->attributes['fitness_status'] = $value;
    }
}
