<?php

namespace App\Domains\Attendance\Contracts;

use App\Domains\Attendance\Models\AttendanceDevice;

interface AttendanceConnectorInterface
{
    /**
     * Test communication and connectivity with the target attendance terminal.
     */
    public function testConnection(AttendanceDevice $device): bool;

    /**
     * Fetch unread/new raw attendance event payloads from the device.
     *
     * @return array<int, array{
     *     employee_identifier: string,
     *     timestamp: string,
     *     event_type: string,
     *     device_event_id: string|null,
     *     payload: array<string, mixed>
     * }>
     */
    public function fetchEvents(AttendanceDevice $device, ?string $sinceTimestamp = null): array;

    /**
     * Acknowledge/clear fetched records if supported by hardware protocol.
     */
    public function acknowledgeEvents(AttendanceDevice $device, array $eventIds): bool;
}
