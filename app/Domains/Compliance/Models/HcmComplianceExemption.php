<?php

namespace App\Domains\Compliance\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmComplianceExemption extends Model
{
    use HasUuids;

    protected $table = 'hcm_compliance_exemptions';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'requirement_id',
        'reason',
        'approved_by',
        'status',
        'effective_from',
        'expiry_date',
        'supporting_document_id',
        'notes',
    ];

    protected $casts = [
        'effective_from' => 'date',
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
        return $this->belongsTo(HcmComplianceRequirement::class, 'requirement_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
