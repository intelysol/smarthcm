<?php

namespace App\Domains\Attendance\Jobs;

use App\Domains\Attendance\Services\PayrollTimeReconciliationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ReconcilePayrollTimeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $exportId,
        public array $payrollProcessedRecords,
        public ?string $payrollBatchId = null,
        public ?int $reconciledById = null
    ) {
    }

    public function handle(PayrollTimeReconciliationService $service): void
    {
        $service->reconcileExportAgainstPayrollRecords(
            $this->exportId,
            $this->payrollProcessedRecords,
            $this->payrollBatchId,
            $this->reconciledById
        );
    }
}