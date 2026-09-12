<?php

namespace App\Domains\Attendance\Services;

use App\Domains\Attendance\Enums\NormalizedEventType;
use App\Domains\Attendance\Models\AttendanceEvent;
use App\Domains\Attendance\Models\AttendanceRawEvent;
use App\Domains\Employee\Models\Employee;
use Carbon\Carbon;
use Carbon\CarbonImmutable;

class AttendanceNormalizer
{
    /**
     * Convert an immutable raw device event into a normalized domain attendance event.
     */
    public function normalizeRawEvent(AttendanceRawEvent $rawEvent): ?AttendanceEvent
    {
        $tenantId = $rawEvent->tenant_id;
        $identifier = $rawEvent->employee_device_identifier;

        // 1. Resolve Employee by identifier (employee_number, employee_code, id)
        $employee = Employee::query()
            ->where('tenant_id', $tenantId)
            ->where(function ($q) use ($identifier) {
                $q->where('employee_number', $identifier)
                    ->orWhere('employee_code', $identifier)
                    ->orWhere('id', $identifier);
            })
            ->first();

        if (! $employee) {
            // Cannot normalize without a matched employee
            $rawEvent->update(['is_processed' => true, 'processed_at' => now()]);
            return null;
        }

        // 2. Resolve Timezone (Device timezone or UTC)
        $timezone = $rawEvent->device?->timezone ?? 'UTC';
        $utcTimestamp = CarbonImmutable::parse($rawEvent->event_timestamp);
        $localTime = $utcTimestamp->setTimezone($timezone);

        // 3. Map Event Type
        $normalizedType = $this->mapEventType($rawEvent->event_type);

        // 4. Generate Normalized Idempotency Key
        $idempotencyKey = hash('sha256', "NORM:{$tenantId}:{$employee->id}:{$utcTimestamp->toIso8601String()}:{$normalizedType->value}");

        $existing = AttendanceEvent::query()
            ->where('tenant_id', $tenantId)
            ->where('idempotency_key', $idempotencyKey)
            ->first();

        if ($existing) {
            $rawEvent->update(['is_processed' => true, 'processed_at' => now()]);
            return $existing;
        }

        $event = AttendanceEvent::query()->create([
            'tenant_id' => $tenantId,
            'raw_event_id' => $rawEvent->id,
            'employee_id' => $employee->id,
            'event_timestamp' => $utcTimestamp->toDateTimeString(),
            'local_date' => $localTime->toDateString(),
            'local_time' => $localTime->format('H:i:s'),
            'timezone' => $timezone,
            'event_type' => $normalizedType->value,
            'source' => $rawEvent->source ?: 'device',
            'location_id' => $rawEvent->device?->location_id ?? $employee->work_location_id,
            'device_id' => $rawEvent->device_id,
            'confidence_status' => 'valid',
            'idempotency_key' => $idempotencyKey,
        ]);

        $rawEvent->update([
            'is_processed' => true,
            'processed_at' => now(),
        ]);

        return $event;
    }

    public function mapEventType(string $rawType): NormalizedEventType
    {
        return match (strtoupper(trim($rawType))) {
            'IN', 'CHECK_IN', '1', 'CLOCK_IN' => NormalizedEventType::CHECK_IN,
            'OUT', 'CHECK_OUT', '2', 'CLOCK_OUT' => NormalizedEventType::CHECK_OUT,
            'BREAK_OUT', 'BREAK_START', '3' => NormalizedEventType::BREAK_START,
            'BREAK_IN', 'BREAK_END', '4' => NormalizedEventType::BREAK_END,
            default => NormalizedEventType::CHECK_IN,
        };
    }

    public function normalize(AttendanceRawEvent $rawEvent): ?AttendanceEvent
    {
        return $this->normalizeRawEvent($rawEvent);
    }
}
