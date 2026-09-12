<?php

namespace App\Domains\HealthSafety\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Branch;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Organization\Models\WorkLocation;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class HcmSafetyIncident extends Model
{
    use HasUuids;

    public const STATUS_REPORTED = 'reported';
    public const STATUS_ACKNOWLEDGED = 'acknowledged';
    public const STATUS_UNDER_INVESTIGATION = 'under_investigation';
    public const STATUS_ACTION_PENDING = 'action_pending';
    public const STATUS_CORRECTIVE_ACTION = 'corrective_action';
    public const STATUS_CLOSED = 'closed';
    public const STATUS_REOPENED = 'reopened';

    protected $table = 'hcm_safety_incidents';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'incident_number',
        'incident_datetime',
        'incident_type',
        'severity',
        'location_description',
        'company_id',
        'branch_id',
        'work_location_id',
        'department_id',
        'description',
        'immediate_action_taken',
        'reported_by',
        'status',
        'is_osha_reportable',
        'lost_time_injury',
        'lost_work_days',
        'closed_at',
        'closed_by',
    ];

    protected $casts = [
        'incident_datetime' => 'datetime',
        'is_osha_reportable' => 'boolean',
        'lost_time_injury' => 'boolean',
        'lost_work_days' => 'integer',
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

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function workLocation(): BelongsTo
    {
        return $this->belongsTo(WorkLocation::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function reportedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function closedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function witnesses(): HasMany
    {
        return $this->hasMany(HcmSafetyIncidentWitness::class, 'safety_incident_id');
    }

    public function investigation(): HasOne
    {
        return $this->hasOne(HcmSafetyIncidentInvestigation::class, 'safety_incident_id');
    }

    public function actions(): HasMany
    {
        return $this->hasMany(HcmSafetyIncidentAction::class, 'safety_incident_id');
    }

    public function getRootCauseSummaryAttribute(): ?string
    {
        return $this->investigation?->root_cause_analysis;
    }
}
