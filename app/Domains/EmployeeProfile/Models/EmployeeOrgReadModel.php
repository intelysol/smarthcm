<?php

namespace App\Domains\EmployeeProfile\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Branch;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Organization\Models\WorkLocation;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeOrgReadModel extends Model
{
    use HasUuids;

    protected $table = 'employee_org_read_models';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'manager_id',
        'company_id',
        'department_id',
        'branch_id',
        'location_id',
        'job_id',
        'position_id',
        'employee_number',
        'full_name',
        'job_title',
        'department_name',
        'branch_name',
        'location_name',
        'hierarchy_path',
        'depth_level',
        'span_of_control',
        'status',
        'photo_path',
        'searchable_text',
    ];

    protected $casts = [
        'depth_level' => 'integer',
        'span_of_control' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(WorkLocation::class, 'location_id');
    }
}
