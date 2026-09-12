<?php

namespace App\Domains\Attendance\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AttendanceRawEvent extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $table = 'attendance_raw_events';

    protected $fillable = [
        'tenant_id',
        'device_id',
        'employee_device_identifier',
        'event_timestamp',
        'event_type',
        'device_event_id',
        'raw_payload',
        'received_at',
        'source',
        'idempotency_key',
        'is_processed',
        'processed_at',
        'created_at',
    ];

    protected $casts = [
        'event_timestamp' => 'datetime',
        'received_at' => 'datetime',
        'processed_at' => 'datetime',
        'created_at' => 'datetime',
        'raw_payload' => 'array',
        'is_processed' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(AttendanceDevice::class, 'device_id');
    }

    public function normalizedEvent(): HasOne
    {
        return $this->hasOne(AttendanceEvent::class, 'raw_event_id');
    }
}
