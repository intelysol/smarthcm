<?php

namespace App\Domains\Compliance\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmEmployeeLicense extends Model
{
    use HasUuids;

    protected $table = 'hcm_employee_licenses';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'compliance_requirement_id',
        'license_type',
        'license_name',
        'license_number',
        'issuing_authority',
        'country',
        'state_province',
        'issue_date',
        'effective_from',
        'expiry_date',
        'status',
        'verification_status',
        'renewal_status',
        'restrictions',
        'document_id',
        'is_current',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'effective_from' => 'date',
        'expiry_date' => 'date',
        'is_current' => 'boolean',
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
