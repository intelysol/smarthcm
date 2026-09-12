<?php

namespace App\Domains\Absence\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\EmployeeRelations\Models\EmployeeRelationCase;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmAbsenceCase extends Model
{
    use HasUuids;

    protected $table = 'hcm_absence_cases';

    protected $fillable = [
        'tenant_id',
        'case_number',
        'employee_id',
        'absence_period_id',
        'hr_case_id',
        'case_type',
        'severity',
        'status',
        'assigned_hr_user_id',
        'trigger_reason',
        'opened_at',
        'closed_at',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function absencePeriod(): BelongsTo
    {
        return $this->belongsTo(HcmAbsencePeriod::class, 'absence_period_id');
    }

    public function hrCase(): BelongsTo
    {
        return $this->belongsTo(EmployeeRelationCase::class, 'hr_case_id');
    }

    public function assignedHrUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_hr_user_id');
    }
}