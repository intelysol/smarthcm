<?php

namespace App\Domains\Lifecycle\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class PersonnelActionRequest extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'personnel_action_requests';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'action_type_id',
        'request_number',
        'status',
        'requested_by',
        'requested_at',
        'effective_date',
        'submitted_at',
        'approved_at',
        'approved_by',
        'executed_at',
        'executed_by',
        'reason',
        'comments',
        'source',
        'priority',
        'workflow_instance_id',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'effective_date' => 'date',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'executed_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function actionType(): BelongsTo
    {
        return $this->belongsTo(PersonnelActionType::class, 'action_type_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function executor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'executed_by');
    }

    public function changes(): HasMany
    {
        return $this->hasMany(PersonnelActionChange::class, 'personnel_action_request_id');
    }

    public function impacts(): HasMany
    {
        return $this->hasMany(PersonnelActionImpact::class, 'personnel_action_request_id');
    }

    public function acknowledgement(): HasOne
    {
        return $this->hasOne(PersonnelActionAcknowledgement::class, 'personnel_action_request_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(PersonnelActionDocument::class, 'personnel_action_request_id');
    }

    public function temporaryAssignment(): HasOne
    {
        return $this->hasOne(PersonnelTemporaryAssignment::class, 'personnel_action_request_id');
    }

    public function audits(): HasMany
    {
        return $this->hasMany(PersonnelActionAudit::class, 'personnel_action_request_id');
    }
}
