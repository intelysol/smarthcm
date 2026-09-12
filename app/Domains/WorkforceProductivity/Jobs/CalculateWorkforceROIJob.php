<?php

namespace App\Domains\WorkforceProductivity\Jobs;

use App\Domains\WorkforceProductivity\Events\WorkforceROICalculated;
use App\Domains\WorkforceProductivity\Services\WorkforceROIService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CalculateWorkforceROIJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly string $tenantId,
        public readonly string $trainingProgramName,
        public readonly float $directTrainingCost,
        public readonly float $trainingHours,
        public readonly float $hourlyWageRate,
        public readonly float $preTrainingHourlyOutput,
        public readonly float $postTrainingHourlyOutput,
        public readonly float $unitValue,
        public readonly int $evaluationPeriodHours = 500,
        public readonly ?string $departmentId = null
    ) {}

    public function handle(WorkforceROIService $service): void
    {
        $calculation = $service->calculateTrainingRoi(
            $this->tenantId,
            $this->trainingProgramName,
            $this->directTrainingCost,
            $this->trainingHours,
            $this->hourlyWageRate,
            $this->preTrainingHourlyOutput,
            $this->postTrainingHourlyOutput,
            $this->unitValue,
            $this->evaluationPeriodHours,
            $this->departmentId
        );

        event(new WorkforceROICalculated($calculation));
    }
}
