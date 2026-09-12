<?php

namespace App\Domains\Attendance\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RosterConflict extends Model
{
    use HasUuids;

    protected $table = 'roster_conflicts';

    protected $fillable = [
        'tenant_id',
        'roster_assignment_id',
        'employee_id',
        'conflict_date',
        'conflict_type',
        'severity',
        'message',
        'resolved_at',
        'resolved_by',
    ];

    protected $casts = [
        'conflict_date' => 'date',
        'resolved_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(RosterAssignment::class, 'roster_assignment_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
