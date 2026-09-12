<?php

namespace App\Domains\Lifecycle\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Department;
use App\Domains\Organization\Models\Position;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonnelTemporaryAssignment extends Model
{
    use HasUuids;

    protected $table = 'personnel_temporary_assignments';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'personnel_action_request_id',
        'assignment_type',
        'home_department_id',
        'temporary_department_id',
        'home_position_id',
        'temporary_position_id',
        'home_manager_id',
        'temporary_manager_id',
        'start_date',
        'end_date',
        'status',
        'reason',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(PersonnelActionRequest::class, 'personnel_action_request_id');
    }

    public function homeDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'home_department_id');
    }

    public function temporaryDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'temporary_department_id');
    }

    public function homePosition(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'home_position_id');
    }

    public function temporaryPosition(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'temporary_position_id');
    }

    public function homeManager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'home_manager_id');
    }

    public function temporaryManager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'temporary_manager_id');
    }
}
