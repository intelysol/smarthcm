<?php

namespace App\Domains\HealthSafety\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HcmMedicalProvider extends Model
{
    use HasUuids;

    protected $table = 'hcm_medical_providers';

    protected $fillable = [
        'tenant_id',
        'name',
        'organization_name',
        'provider_type',
        'license_number',
        'country',
        'city',
        'contact_email',
        'contact_phone',
        'status',
        'is_verified',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(HcmMedicalAssessment::class, 'medical_provider_id');
    }

    public function fitnessRecords(): HasMany
    {
        return $this->hasMany(HcmMedicalFitnessRecord::class, 'medical_provider_id');
    }
}
