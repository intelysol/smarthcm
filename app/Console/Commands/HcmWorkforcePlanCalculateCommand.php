<?php

namespace App\Console\Commands;

use App\Domains\WorkforcePlanning\Models\HcmWorkforcePlan;
use App\Domains\WorkforcePlanning\Services\HeadcountPlanningService;
use App\Domains\WorkforcePlanning\Services\LaborCostPlanningService;
use Illuminate\Console\Command;

class HcmWorkforcePlanCalculateCommand extends Command
{
    protected $signature = 'hcm:workforce-plan-calculate {plan_id? : ID of workforce plan to calculate} {--tenant= : Tenant ID}';
    protected $description = 'Calculates deterministic headcount balance and labor cost totals for a workforce plan.';

    public function handle(
        HeadcountPlanningService $headcountService,
        LaborCostPlanningService $costService
    ): int {
        $planId = $this->argument('plan_id');
        $tenantId = $this->option('tenant');

        $query = HcmWorkforcePlan::query();
        if ($planId) {
            $query->where('id', $planId);
        }
        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        $plans = $query->get();

        if ($plans->isEmpty()) {
            $this->warn('No workforce plans found to calculate.');
            return 0;
        }

        foreach ($plans as $plan) {
            $this->info("Calculating workforce plan: {$plan->name} ({$plan->code})...");
            $headcountService->initializeFromCoreHr($plan, $plan->department_id);
            $costSummary = $costService->calculateTotalLaborCost($plan->tenant_id, $plan->id);

            $this->line("  - Total Budgeted Labor Cost: {$costSummary['total_budgeted']} {$plan->currency}");
            $this->line("  - Total Forecast Labor Cost: {$costSummary['total_forecast']} {$plan->currency}");
        }

        $this->info('Workforce plan calculation complete.');
        return 0;
    }
}
