<?php

namespace App\Domains\Attendance\Jobs;

use App\Domains\Attendance\Models\AttendanceDevice;
use App\Domains\Attendance\Services\AttendanceDeviceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncAttendanceDeviceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $deviceId
    ) {}

    public function handle(AttendanceDeviceService $deviceService): void
    {
        $device = AttendanceDevice::query()->find($this->deviceId);
        if ($device && $device->is_active) {
            $deviceService->syncDevice($device);
        }
    }
}
