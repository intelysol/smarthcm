<?php

namespace App\Domains\Attendance\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceEvent extends Model
{
    use HasUuids;

    protected $table = 'attendance_events';

    protected $fillable = [
        'tenant_id',
        'raw_event_id',
        'employee_id',
        'event_timestamp',
        'local_date',
        'local_time',
        'timezone',
        'event_type',
        'source',
        'location_id',
        'device_id',
        'confidence_status',
        'idempotency_key',
    ];

    protected $casts = [
        'event_timestamp' => 'datetime',
        'local_date' => 'date',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function rawEvent(): BelongsTo
    {
        return $this->belongsTo(AttendanceRawEvent::class, 'raw_event_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(AttendanceDevice::class, 'device_id');
    }
}
