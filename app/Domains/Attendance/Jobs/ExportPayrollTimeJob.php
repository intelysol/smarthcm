<?php

namespace App\Domains\Attendance\Jobs;

use App\Domains\Attendance\Services\PayrollTimeExportService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExportPayrollTimeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $tenantId,
        public string $periodStart,
        public string $periodEnd,
        public ?string $attendancePeriodId = null,
        public ?int $exportedById = null
    ) {
    }

    public function handle(PayrollTimeExportService $service): void
    {
        $service->generatePayrollExport(
            $this->tenantId,
            Carbon::parse($this->periodStart),
            Carbon::parse($this->periodEnd),
            $this->attendancePeriodId,
            $this->exportedById
        );
    }
}