<?php

namespace App\Domains\Attendance\Jobs;

use App\Domains\Attendance\Models\AttendanceRawEvent;
use App\Domains\Attendance\Services\AttendanceNormalizer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NormalizeAttendanceEventsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $tenantId
    ) {}

    public function handle(AttendanceNormalizer $normalizer): void
    {
        $unprocessed = AttendanceRawEvent::query()
            ->where('tenant_id', $this->tenantId)
            ->where('is_processed', false)
            ->limit(500)
            ->get();

        foreach ($unprocessed as $rawEvent) {
            $normalizer->normalizeRawEvent($rawEvent);
        }
    }
}
