<?php

namespace App\Domains\Attendance\Jobs;

use App\Domains\Attendance\Models\AttendanceRawEvent;
use App\Domains\Attendance\Services\AttendanceNormalizer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessRawAttendanceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $rawEventId)
    {
    }

    public function handle(AttendanceNormalizer $normalizer): void
    {
        $rawEvent = AttendanceRawEvent::find($this->rawEventId);
        if ($rawEvent && ! $rawEvent->is_processed) {
            $normalizer->normalize($rawEvent);
        }
    }
}