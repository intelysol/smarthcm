<?php

namespace App\Console\Commands;

use App\Domains\WorkforcePlanning\Models\HcmWorkforceScenario;
use App\Domains\WorkforcePlanning\Services\WorkforceScenarioService;
use Illuminate\Console\Command;

class HcmWorkforceScenarioSimulateCommand extends Command
{
    protected $signature = 'hcm:workforce-scenario-simulate {scenario_id? : ID of workforce scenario to simulate}';
    protected $description = 'Simulates what-if projections for workforce scenarios (Growth, Cost Reduction, Freeze, etc.).';

    public function handle(WorkforceScenarioService $scenarioService): int
    {
        $scenarioId = $this->argument('scenario_id');

        $query = HcmWorkforceScenario::query();
        if ($scenarioId) {
            $query->where('id', $scenarioId);
        }

        $scenarios = $query->get();

        if ($scenarios->isEmpty()) {
            $this->warn('No workforce scenarios found to simulate.');
            return 0;
        }

        foreach ($scenarios as $scenario) {
            $this->info("Simulating scenario: {$scenario->name} ({$scenario->scenario_type})...");
            $result = $scenarioService->simulateScenario($scenario);

            $this->line("  - Projected Closing Headcount: {$result['projected_closing_headcount']}");
            $this->line("  - Total Projected Labor Cost: {$result['total_projected_cost']}");
            $this->line("  - Cost Variance vs Base Plan: {$result['cost_variance_vs_base']}");
        }

        $this->info('Workforce scenario simulation complete.');
        return 0;
    }
}
