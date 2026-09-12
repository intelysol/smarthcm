<?php

namespace App\Domains\Attendance\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmScheduleChangeLog extends Model
{
    use HasUuids;

    protected $table = 'hcm_schedule_change_logs';

    protected $fillable = [
        'tenant_id',
        'roster_assignment_id',
        'previous_shift_id',
        'new_shift_id',
        'previous_date',
        'new_date',
        'reason',
        'changed_by',
    ];

    protected $casts = [
        'previous_date' => 'date',
        'new_date' => 'date',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(RosterAssignment::class, 'roster_assignment_id');
    }

    public function previousShift(): BelongsTo
    {
        return $this->belongsTo(ShiftDefinition::class, 'previous_shift_id');
    }

    public function newShift(): BelongsTo
    {
        return $this->belongsTo(ShiftDefinition::class, 'new_shift_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
