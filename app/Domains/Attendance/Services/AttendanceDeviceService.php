<?php

namespace App\Domains\Attendance\Services;

use App\Domains\Attendance\Connectors\APIAttendanceConnector;
use App\Domains\Attendance\Connectors\BiometricConnector;
use App\Domains\Attendance\Connectors\CSVAttendanceConnector;
use App\Domains\Attendance\Connectors\MobileAttendanceConnector;
use App\Domains\Attendance\Connectors\RFIDConnector;
use App\Domains\Attendance\Connectors\ZKTecoConnector;
use App\Domains\Attendance\Contracts\AttendanceConnectorInterface;
use App\Domains\Attendance\Models\AttendanceDevice;
use App\Domains\Attendance\Models\AttendanceRawEvent;
use App\Domains\Attendance\Models\AttendanceSyncRun;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AttendanceDeviceService
{
    public function __construct(
        protected AttendanceNormalizer $normalizer
    ) {}

    public function getConnector(string $connectorType): AttendanceConnectorInterface
    {
        return match ($connectorType) {
            'zkteco' => app(ZKTecoConnector::class),
            'biometric' => app(BiometricConnector::class),
            'rfid' => app(RFIDConnector::class),
            'mobile' => app(MobileAttendanceConnector::class),
            'csv' => app(CSVAttendanceConnector::class),
            'api' => app(APIAttendanceConnector::class),
            default => app(ZKTecoConnector::class),
        };
    }

    public function testConnection(AttendanceDevice $device): bool
    {
        $connector = $this->getConnector($device->connector_type);
        $connected = $connector->testConnection($device);

        $device->update([
            'connection_status' => $connected ? 'online' : 'offline',
            'last_synced_at' => now(),
        ]);

        return $connected;
    }

    /**
     * Ingest an array of raw event payloads into the immutable raw event ledger.
     *
     * @param array<int, array{
     *     employee_identifier: string,
     *     timestamp: string,
     *     event_type: string,
     *     device_event_id?: string|null,
     *     payload?: array<string, mixed>
     * }> $events
     * @return array{received: int, imported: int, duplicates: int}
     */
    public function ingestRawEvents(string $tenantId, ?AttendanceDevice $device, array $events, string $source = 'device'): array
    {
        $received = count($events);
        $imported = 0;
        $duplicates = 0;

        foreach ($events as $event) {
            $identifier = trim((string) ($event['employee_identifier'] ?? ''));
            $timestamp = $event['timestamp'] ?? now()->toIso8601String();
            $eventType = strtoupper((string) ($event['event_type'] ?? 'UNKNOWN'));
            $deviceEventId = $event['device_event_id'] ?? null;

            // Generate deterministic idempotency key
            $idempotencyKey = hash('sha256', "{$tenantId}:{$device?->id}:{$identifier}:{$timestamp}:{$eventType}:{$deviceEventId}");

            // Check duplicate
            $exists = AttendanceRawEvent::query()
                ->where('tenant_id', $tenantId)
                ->where('idempotency_key', $idempotencyKey)
                ->exists();

            if ($exists) {
                $duplicates++;
                continue;
            }

            $rawEvent = AttendanceRawEvent::query()->create([
                'tenant_id' => $tenantId,
                'device_id' => $device?->id,
                'employee_device_identifier' => $identifier,
                'event_timestamp' => $timestamp,
                'event_type' => $eventType,
                'device_event_id' => $deviceEventId,
                'raw_payload' => $event['payload'] ?? $event,
                'source' => $source,
                'idempotency_key' => $idempotencyKey,
                'is_processed' => false,
            ]);

            // Immediately normalize the event
            $this->normalizer->normalizeRawEvent($rawEvent);
            $imported++;
        }

        return [
            'received' => $received,
            'imported' => $imported,
            'duplicates' => $duplicates,
        ];
    }

    public function syncDevice(AttendanceDevice $device, ?User $actor = null): AttendanceSyncRun
    {
        $syncRun = AttendanceSyncRun::query()->create([
            'tenant_id' => $device->tenant_id,
            'device_id' => $device->id,
            'started_at' => now(),
            'status' => 'running',
            'triggered_by' => $actor?->id,
        ]);

        try {
            $connector = $this->getConnector($device->connector_type);
            $events = $connector->fetchEvents($device, $device->last_synced_at?->toIso8601String());

            $result = $this->ingestRawEvents($device->tenant_id, $device, $events, $device->connector_type);

            $syncRun->update([
                'completed_at' => now(),
                'status' => 'success',
                'events_received' => $result['received'],
                'events_imported' => $result['imported'],
                'events_duplicated' => $result['duplicates'],
                'events_failed' => 0,
            ]);

            $device->update([
                'last_synced_at' => now(),
                'connection_status' => 'online',
            ]);
        } catch (\Throwable $e) {
            $syncRun->update([
                'completed_at' => now(),
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            $device->update([
                'connection_status' => 'error',
            ]);
        }

        return $syncRun;
    }
}
