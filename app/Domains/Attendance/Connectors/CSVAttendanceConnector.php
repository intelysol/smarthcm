<?php

namespace App\Domains\Attendance\Connectors;

use App\Domains\Attendance\Contracts\AttendanceConnectorInterface;
use App\Domains\Attendance\Models\AttendanceDevice;

class CSVAttendanceConnector implements AttendanceConnectorInterface
{
    public function testConnection(AttendanceDevice $device): bool
    {
        return $device->is_active;
    }

    public function fetchEvents(AttendanceDevice $device, ?string $sinceTimestamp = null): array
    {
        return [];
    }

    public function acknowledgeEvents(AttendanceDevice $device, array $eventIds): bool
    {
        return true;
    }
}
