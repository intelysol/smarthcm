<?php

namespace App\Domains\Attendance\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmShiftSwapRequest extends Model
{
    use HasUuids;

    protected $table = 'hcm_shift_swap_requests';

    protected $fillable = [
        'tenant_id',
        'requesting_assignment_id',
        'target_assignment_id',
        'requesting_employee_id',
        'target_employee_id',
        'status',
        'validation_payload',
        'peer_response_at',
        'approved_by',
        'approved_at',
        'reason',
    ];

    protected $casts = [
        'validation_payload' => 'array',
        'peer_response_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function requestingAssignment(): BelongsTo
    {
        return $this->belongsTo(RosterAssignment::class, 'requesting_assignment_id');
    }

    public function targetAssignment(): BelongsTo
    {
        return $this->belongsTo(RosterAssignment::class, 'target_assignment_id');
    }

    public function requestingEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'requesting_employee_id');
    }

    public function targetEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'target_employee_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
