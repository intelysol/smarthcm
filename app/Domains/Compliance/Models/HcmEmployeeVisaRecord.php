<?php

namespace App\Domains\Compliance\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmEmployeeVisaRecord extends Model
{
    use HasUuids;

    protected $table = 'hcm_employee_visa_records';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'compliance_requirement_id',
        'visa_type',
        'visa_number',
        'issuing_country',
        'issuing_authority',
        'issue_date',
        'effective_from',
        'expiry_date',
        'entry_date',
        'exit_date',
        'is_multiple_entry',
        'sponsor',
        'residency_status',
        'residency_number',
        'status',
        'verification_status',
        'notes',
        'document_id',
        'is_current',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'effective_from' => 'date',
        'expiry_date' => 'date',
        'entry_date' => 'date',
        'exit_date' => 'date',
        'is_multiple_entry' => 'boolean',
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
