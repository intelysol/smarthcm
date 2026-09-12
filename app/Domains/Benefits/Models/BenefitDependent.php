<?php

namespace App\Domains\Benefits\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BenefitDependent extends Model
{
    use HasUuids;

    protected $table = 'benefit_dependents';

    protected $fillable = [
        'tenant_id',
        'benefit_enrollment_id',
        'employee_id',
        'family_member_id',
        'name',
        'relationship',
        'date_of_birth',
        'gender',
        'national_id',
        'is_eligible',
        'effective_from',
        'effective_to',
        'coverage',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'is_eligible' => 'boolean',
        'coverage' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(BenefitEnrollment::class, 'benefit_enrollment_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
