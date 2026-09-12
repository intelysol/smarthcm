<?php

namespace App\Domains\Attendance\Models;

use App\Domains\Organization\Models\Branch;
use App\Domains\Organization\Models\WorkLocation;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttendanceDevice extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'attendance_devices';

    protected $fillable = [
        'tenant_id',
        'device_code',
        'name',
        'vendor',
        'model',
        'serial_number',
        'ip_address',
        'port',
        'location_id',
        'branch_id',
        'timezone',
        'connector_type',
        'secret_reference',
        'is_active',
        'last_synced_at',
        'connection_status',
        'metadata',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'port' => 'integer',
        'is_active' => 'boolean',
        'last_synced_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(WorkLocation::class, 'location_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function syncRuns(): HasMany
    {
        return $this->hasMany(AttendanceSyncRun::class, 'device_id')->latest('started_at');
    }

    public function rawEvents(): HasMany
    {
        return $this->hasMany(AttendanceRawEvent::class, 'device_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
