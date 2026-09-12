<?php

namespace App\Domains\Offboarding\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class SeparationRequest extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'separation_requests';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'separation_type_id',
        'request_number',
        'status',
        'requested_by',
        'requested_at',
        'submitted_at',
        'approved_at',
        'approved_by',
        'effective_date',
        'notice_start_date',
        'proposed_last_working_day',
        'approved_last_working_day',
        'actual_last_working_day',
        'executed_at',
        'executed_by',
        'reason',
        'comments',
        'source',
        'er_case_reference_id',
        'is_garden_leave',
        'garden_leave_start_date',
        'garden_leave_end_date',
        'workflow_instance_id',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'executed_at' => 'datetime',
        'effective_date' => 'date',
        'notice_start_date' => 'date',
        'proposed_last_working_day' => 'date',
        'approved_last_working_day' => 'date',
        'actual_last_working_day' => 'date',
        'garden_leave_start_date' => 'date',
        'garden_leave_end_date' => 'date',
        'is_garden_leave' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function separationType(): BelongsTo
    {
        return $this->belongsTo(SeparationType::class, 'separation_type_id');
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

    public function impacts(): HasMany
    {
        return $this->hasMany(SeparationImpact::class, 'separation_request_id');
    }

    public function noticePeriod(): HasOne
    {
        return $this->hasOne(SeparationNoticePeriod::class, 'separation_request_id');
    }

    public function clearances(): HasMany
    {
        return $this->hasMany(SeparationClearance::class, 'separation_request_id');
    }

    public function handoverRecord(): HasOne
    {
        return $this->hasOne(SeparationHandoverRecord::class, 'separation_request_id');
    }

    public function finalSettlement(): HasOne
    {
        return $this->hasOne(SeparationFinalSettlement::class, 'separation_request_id');
    }

    public function exitInterview(): HasOne
    {
        return $this->hasOne(SeparationExitInterview::class, 'separation_request_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(SeparationDocument::class, 'separation_request_id');
    }

    public function reversals(): HasMany
    {
        return $this->hasMany(SeparationReversal::class, 'separation_request_id');
    }

    public function audits(): HasMany
    {
        return $this->hasMany(SeparationAudit::class, 'separation_request_id');
    }
}
