<?php

namespace App\Domains\WorkforceProductivity\Jobs;

use App\Domains\WorkforceProductivity\Services\LaborEfficiencyService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CalculateLaborEfficiencyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly float $outputVolume,
        public readonly float $totalLaborHours,
        public readonly float $productiveHours,
        public readonly float $paidHours,
        public readonly float $overtimeHours,
        public readonly float $totalFte,
        public readonly float $totalWorkforceCost
    ) {}

    public function handle(LaborEfficiencyService $service): void
    {
        $service->computeEfficiency(
            $this->outputVolume,
            $this->totalLaborHours,
            $this->productiveHours,
            $this->paidHours,
            $this->overtimeHours,
            $this->totalFte,
            $this->totalWorkforceCost
        );
    }
}
