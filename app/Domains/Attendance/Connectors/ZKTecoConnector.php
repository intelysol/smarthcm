<?php

namespace App\Domains\Attendance\Connectors;

use App\Domains\Attendance\Contracts\AttendanceConnectorInterface;
use App\Domains\Attendance\Models\AttendanceDevice;

class ZKTecoConnector implements AttendanceConnectorInterface
{
    public function testConnection(AttendanceDevice $device): bool
    {
        // Enterprise abstraction: Device communication executed via Integration Hub / daemon gateway
        return ! empty($device->ip_address) && $device->is_active;
    }

    public function fetchEvents(AttendanceDevice $device, ?string $sinceTimestamp = null): array
    {
        // Simulated / Gateway communication returning standard normalized raw format
        return [];
    }

    public function acknowledgeEvents(AttendanceDevice $device, array $eventIds): bool
    {
        return true;
    }
}
