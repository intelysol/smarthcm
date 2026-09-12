<?php

namespace App\Domains\Absence\Jobs;

use App\Domains\Absence\Services\AbsenceReconciliationService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ReconcileAbsenceRecordsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $tenantId,
        public string $periodStart,
        public string $periodEnd,
        public ?int $reconciledById = null
    ) {
    }

    public function handle(AbsenceReconciliationService $service): void
    {
        $service->reconcilePeriod(
            $this->tenantId,
            Carbon::parse($this->periodStart),
            Carbon::parse($this->periodEnd),
            $this->reconciledById
        );
    }
}