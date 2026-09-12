<?php

namespace App\Domains\Attendance\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttendancePolicy extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'attendance_policies';

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'description',
        'grace_period_minutes',
        'late_threshold_minutes',
        'early_departure_threshold_minutes',
        'half_day_late_threshold_minutes',
        'minimum_working_hours_minutes',
        'overtime_minimum_minutes',
        'overtime_approval_required',
        'rounding_interval_minutes',
        'rounding_method',
        'auto_deduct_breaks',
        'missing_punch_policy',
        'rules',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'rules' => 'array',
        'grace_period_minutes' => 'integer',
        'late_threshold_minutes' => 'integer',
        'early_departure_threshold_minutes' => 'integer',
        'half_day_late_threshold_minutes' => 'integer',
        'minimum_working_hours_minutes' => 'integer',
        'overtime_minimum_minutes' => 'integer',
        'overtime_approval_required' => 'boolean',
        'rounding_interval_minutes' => 'integer',
        'auto_deduct_breaks' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(AttendancePolicyAssignment::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
