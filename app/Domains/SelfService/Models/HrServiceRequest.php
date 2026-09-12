<?php

namespace App\Domains\SelfService\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\EmployeeRelations\Models\EmployeeRelationCase;
use App\Domains\Organization\Models\Branch;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\CostCenter;
use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class HrServiceRequest extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'hr_service_requests';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'hr_service_definition_id',
        'hr_service_version_id',
        'request_number',
        'subject',
        'description',
        'priority',
        'status',
        'confidentiality_level',
        'company_id',
        'branch_id',
        'department_id',
        'cost_center_id',
        'reporting_manager_id',
        'assigned_queue_id',
        'assigned_user_id',
        'sla_instance_id',
        'due_at',
        'first_response_at',
        'resolved_at',
        'closed_at',
        'source_domain_module',
        'source_entity_type',
        'source_entity_id',
        'employee_relation_case_id',
        'form_data',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'first_response_at' => 'datetime',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
        'form_data' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(HrServiceDefinition::class, 'hr_service_definition_id');
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(HrServiceVersion::class, 'hr_service_version_id');
    }

    public function assignedQueue(): BelongsTo
    {
        return $this->belongsTo(HrServiceQueue::class, 'assigned_queue_id');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function reportingManager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'reporting_manager_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class);
    }

    public function erCase(): BelongsTo
    {
        return $this->belongsTo(EmployeeRelationCase::class, 'employee_relation_case_id');
    }

    public function slaInstance(): HasOne
    {
        return $this->hasOne(HrServiceSlaInstance::class, 'hr_service_request_id');
    }

    public function fields(): HasMany
    {
        return $this->hasMany(HrServiceRequestField::class, 'hr_service_request_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(HrServiceRequestComment::class, 'hr_service_request_id');
    }

    public function publicComments(): HasMany
    {
        return $this->hasMany(HrServiceRequestComment::class, 'hr_service_request_id')->where('comment_type', 'public');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(HrServiceRequestAssignment::class, 'hr_service_request_id');
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(HrServiceRequestStatusHistory::class, 'hr_service_request_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(HrServiceRequestDocument::class, 'hr_service_request_id');
    }

    public function generatedDocuments(): HasMany
    {
        return $this->hasMany(HrServiceGeneratedDocument::class, 'hr_service_request_id');
    }

    public function escalations(): HasMany
    {
        return $this->hasMany(HrServiceEscalation::class, 'hr_service_request_id');
    }

    public function parentLinks(): HasMany
    {
        return $this->hasMany(HrServiceRequestLink::class, 'child_request_id');
    }

    public function childLinks(): HasMany
    {
        return $this->hasMany(HrServiceRequestLink::class, 'parent_request_id');
    }
}
