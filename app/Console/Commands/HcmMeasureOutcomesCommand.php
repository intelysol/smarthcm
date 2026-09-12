<?php

namespace App\Console\Commands;

use App\Domains\WorkforceOptimization\Models\HcmWorkforceOptimizationRecommendation;
use App\Domains\WorkforceOptimization\Services\OptimizationOutcomeService;
use Illuminate\Console\Command;

class HcmMeasureOutcomesCommand extends Command
{
    protected $signature = 'hcm:measure-outcomes 
                            {--rec= : Recommendation ID to measure outcome for}
                            {--category=COST : Metric category (COST, CAPACITY, PRODUCTIVITY)}
                            {--metric=LaborVariance : Metric name}
                            {--baseline=0 : Baseline value}
                            {--actual=0 : Actual realized value}
                            {--window=30 : Window in days}';

    protected $description = 'Measure realized outcomes against predicted optimization impacts';

    public function handle(OptimizationOutcomeService $outcomeService): int
    {
        $recId = $this->option('rec');
        $category = $this->option('category');
        $metric = $this->option('metric');
        $baseline = (float) $this->option('baseline');
        $actual = (float) $this->option('actual');
        $window = (int) $this->option('window');

        $rec = HcmWorkforceOptimizationRecommendation::find($recId);
        if (!$rec) {
            $this->error("Recommendation [{$recId}] not found.");
            return Command::FAILURE;
        }

        $outcome = $outcomeService->measureOutcome(
            recommendation: $rec,
            metricCategory: $category,
            metricName: $metric,
            baselineValue: $baseline,
            actualValue: $actual,
            windowDays: $window
        );

        $this->info("Outcome measurement recorded successfully!");
        $this->table(
            ['Metric', 'Baseline', 'Predicted', 'Actual', 'Variance', 'Status'],
            [
                [$metric, $outcome->baseline_value, $outcome->predicted_value, $outcome->actual_value, "{$outcome->variance_pct}%", $outcome->realization_status]
            ]
        );

        return Command::SUCCESS;
    }
}
