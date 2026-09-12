<?php

namespace App\Domains\Onboarding\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class HcmOnboardingCaseTask extends Model
{
    use HasUuids;

    protected $table = 'hcm_onboarding_case_tasks';

    protected $fillable = [
        'tenant_id',
        'case_id',
        'template_task_id',
        'title',
        'description',
        'task_type',
        'owner_role',
        'assigned_to_user_id',
        'assigned_to_employee_id',
        'due_date',
        'status',
        'is_required',
        'completed_at',
        'completed_by',
        'notes',
    ];

    protected $casts = [
        'due_date' => 'date',
        'is_required' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function case(): BelongsTo
    {
        return $this->belongsTo(HcmOnboardingCase::class, 'case_id');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function assignedEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'assigned_to_employee_id');
    }

    public function completer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    // Prerequisite tasks that this task depends on
    public function prerequisites(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'hcm_onboarding_task_dependencies',
            'task_id',
            'depends_on_task_id'
        )->withTimestamps();
    }

    // Downstream tasks that depend on this task
    public function dependents(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'hcm_onboarding_task_dependencies',
            'depends_on_task_id',
            'task_id'
        )->withTimestamps();
    }
}
