<?php

namespace App\Domains\Compliance\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmEmployeeRegistration extends Model
{
    use HasUuids;

    protected $table = 'hcm_employee_registrations';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'compliance_requirement_id',
        'registration_type',
        'registration_number',
        'authority_name',
        'registration_date',
        'expiry_date',
        'status',
        'verification_status',
        'document_id',
    ];

    protected $casts = [
        'registration_date' => 'date',
        'expiry_date' => 'date',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function requirement(): BelongsTo
    {
        return $this->belongsTo(HcmComplianceRequirement::class, 'compliance_requirement_id');
    }
}
