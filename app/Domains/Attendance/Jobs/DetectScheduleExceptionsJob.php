<?php

namespace App\Domains\Attendance\Jobs;

use App\Domains\Attendance\Services\Scheduling\RealTimeCoverageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DetectScheduleExceptionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $tenantId,
        public string $date,
        public ?string $departmentId = null
    ) {}

    public function handle(RealTimeCoverageService $realTimeService): void
    {
        $realTimeService->evaluateRealTimeCoverage($this->tenantId, $this->date, $this->departmentId);
    }
}
