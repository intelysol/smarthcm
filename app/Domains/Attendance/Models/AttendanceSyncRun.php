<?php

namespace App\Domains\Attendance\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceSyncRun extends Model
{
    use HasUuids;

    protected $table = 'attendance_sync_runs';

    protected $fillable = [
        'tenant_id',
        'device_id',
        'started_at',
        'completed_at',
        'status',
        'events_received',
        'events_imported',
        'events_failed',
        'events_duplicated',
        'error_message',
        'triggered_by',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'events_received' => 'integer',
        'events_imported' => 'integer',
        'events_failed' => 'integer',
        'events_duplicated' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(AttendanceDevice::class, 'device_id');
    }

    public function triggerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }
}
