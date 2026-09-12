<?php

namespace App\Domains\Mobility\Models;

use App\Domains\Organization\Models\Company;
use App\Domains\Employee\Models\Employee;
use App\Domains\Lifecycle\Models\PersonnelActionRequest;
use App\Domains\Organization\Models\Department;
use App\Domains\Organization\Models\Position;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MobilityRepatriation extends Model
{
    use HasUuids;

    protected $table = 'hcm_mobility_repatriations';

    protected $fillable = [
        'tenant_id',
        'assignment_id',
        'repatriation_number',
        'planned_return_date',
        'actual_return_date',
        'outcome_type',
        'return_company_id',
        'return_department_id',
        'return_position_id',
        'return_manager_id',
        'status',
        'expense_settlement_completed',
        'advance_settlement_completed',
        'compliance_closure_completed',
        'payroll_transition_completed',
        'personnel_action_request_id',
        'notes',
    ];

    protected $casts = [
        'planned_return_date' => 'date',
        'actual_return_date' => 'date',
        'expense_settlement_completed' => 'boolean',
        'advance_settlement_completed' => 'boolean',
        'compliance_closure_completed' => 'boolean',
        'payroll_transition_completed' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(MobilityAssignment::class, 'assignment_id');
    }

    public function returnCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'return_company_id');
    }

    public function returnDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'return_department_id');
    }

    public function returnPosition(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'return_position_id');
    }

    public function returnManager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'return_manager_id');
    }

    public function personnelActionRequest(): BelongsTo
    {
        return $this->belongsTo(PersonnelActionRequest::class, 'personnel_action_request_id');
    }
}
