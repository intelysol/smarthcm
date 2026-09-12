<?php

namespace App\Console\Commands;

use App\Domains\WorkforceOptimization\Contracts\WorkforceOptimizationInterface;
use Illuminate\Console\Command;

class HcmRunWorkforceOptimizationCommand extends Command
{
    protected $signature = 'hcm:optimization-run 
                            {--tenant= : Tenant ID to run optimization for}
                            {--model= : Optimization model code to apply}
                            {--dept= : Optional specific department filter}';

    protected $description = 'Execute workforce optimization engine run to solve multi-objective trade-offs';

    public function handle(WorkforceOptimizationInterface $optimizationService): int
    {
        $tenantId = $this->option('tenant') ?? '00000000-0000-0000-0000-000000000001';
        $modelCode = $this->option('model');
        $dept = $this->option('dept');

        $this->info("Initiating Workforce Optimization Run for tenant: {$tenantId}...");

        $scope = [];
        if ($dept) {
            $scope['department_id'] = $dept;
        }

        try {
            $run = $optimizationService->runOptimization(
                tenantId: $tenantId,
                modelCode: $modelCode,
                scope: $scope
            );

            $this->info("Optimization Run [{$run->run_code}] Completed Successfully!");
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Run ID', $run->id],
                    ['Model Code', $run->model?->model_code ?? 'DEFAULT'],
                    ['Status', $run->status],
                    ['Execution Time (ms)', $run->execution_time_ms],
                    ['Recommendations Count', count($run->recommendations)],
                ]
            );

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Optimization run failed: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
