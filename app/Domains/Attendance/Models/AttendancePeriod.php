<?php

namespace App\Domains\Attendance\Models;

use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttendancePeriod extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'attendance_periods';

    protected $fillable = [
        'tenant_id',
        'company_id',
        'period_name',
        'start_date',
        'end_date',
        'status',
        'locked_at',
        'locked_by',
        'reopened_at',
        'reopened_by',
        'reopen_reason',
        'closed_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'locked_at' => 'datetime',
        'reopened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function timesheets(): HasMany
    {
        return $this->hasMany(Timesheet::class);
    }

    public function lockUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function reopenUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reopened_by');
    }

    public function isLocked(): bool
    {
        return in_array($this->status, ['locked', 'closed'], true);
    }
}
