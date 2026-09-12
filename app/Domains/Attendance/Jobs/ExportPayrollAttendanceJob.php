<?php

namespace App\Domains\Attendance\Jobs;

use App\Domains\Attendance\Services\AttendanceExportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExportPayrollAttendanceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $tenantId,
        public string $startDate,
        public string $endDate
    ) {}

    public function handle(AttendanceExportService $exportService): array
    {
        return $exportService->generatePayrollExportPayload($this->tenantId, $this->startDate, $this->endDate);
    }
}
