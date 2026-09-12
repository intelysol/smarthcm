<?php

namespace App\Domains\HealthSafety\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmMedicalRestriction extends Model
{
    use HasUuids;

    protected $table = 'hcm_medical_restrictions';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'fitness_record_id',
        'restriction_type',
        'title',
        'operational_description',
        'medical_rationale_restricted',
        'effective_from',
        'effective_to',
        'is_permanent',
        'status',
        'issued_by',
        'document_id',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_to' => 'date',
        'is_permanent' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function fitnessRecord(): BelongsTo
    {
        return $this->belongsTo(HcmMedicalFitnessRecord::class, 'fitness_record_id');
    }

    public function issuedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}
