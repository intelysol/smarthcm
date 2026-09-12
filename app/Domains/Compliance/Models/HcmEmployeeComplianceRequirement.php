<?php

namespace App\Domains\Compliance\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmEmployeeComplianceRequirement extends Model
{
    use HasUuids;

    protected $table = 'hcm_employee_compliance_requirements';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'requirement_id',
        'status',
        'due_date',
        'fulfilled_at',
        'effective_from',
        'effective_to',
        'notes',
    ];

    protected $casts = [
        'due_date' => 'date',
        'fulfilled_at' => 'datetime',
        'effective_from' => 'date',
        'effective_to' => 'date',
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
}
