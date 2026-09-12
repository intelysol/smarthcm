<?php

namespace App\Domains\WorkforcePlanning\Services;

use App\Domains\WorkforcePlanning\Enums\WorkforceScenarioType;
use App\Domains\WorkforcePlanning\Models\HcmWorkforcePlan;
use App\Domains\WorkforcePlanning\Models\HcmWorkforceScenario;
use App\Domains\WorkforcePlanning\Models\HcmWorkforceScenarioCost;
use App\Domains\WorkforcePlanning\Models\HcmWorkforceScenarioHeadcount;
use App\Domains\WorkforcePlanning\Models\HcmWorkforceScenarioPosition;
use Illuminate\Support\Facades\DB;

class WorkforceScenarioService
{
    public function createScenario(HcmWorkforcePlan $basePlan, array $data, ?int $userId = null): HcmWorkforceScenario
    {
        return HcmWorkforceScenario::create([
            'tenant_id' => $basePlan->tenant_id,
            'base_plan_id' => $basePlan->id,
            'name' => $data['name'],
            'scenario_type' => $data['scenario_type'] ?? WorkforceScenarioType::BASE->value,
            'description' => $data['description'] ?? null,
            'status' => 'draft',
            'created_by' => $userId,
        ]);
    }

    public function simulateScenario(HcmWorkforceScenario $scenario): array
    {
        return DB::transaction(function () use ($scenario) {
            $basePlan = $scenario->basePlan;
            $type = $scenario->scenario_type;

            // Multipliers according to scenario definition
            $headcountMultiplier = match ($type) {
                WorkforceScenarioType::GROWTH->value => 1.15,
                WorkforceScenarioType::COST_REDUCTION->value => 0.88,
                WorkforceScenarioType::HIRING_FREEZE->value => 0.96,
                default => 1.00,
            };

            $costMultiplier = match ($type) {
                WorkforceScenarioType::GROWTH->value => 1.18,
                WorkforceScenarioType::COST_REDUCTION->value => 0.85,
                WorkforceScenarioType::HIRING_FREEZE->value => 0.94,
                default => 1.00,
            };

            // 1. Calculate Scenario Headcount
            $baseHeadcounts = $basePlan->headcountPlans()->with('period')->get();
            HcmWorkforceScenarioHeadcount::where('scenario_id', $scenario->id)->delete();

            $totalProjectedHeadcount = 0;
            $totalProjectedHires = 0;
            $totalProjectedExits = 0;

            if ($baseHeadcounts->isEmpty()) {
                // Generate default 12 period projections
                $periods = $basePlan->periods()->orderBy('period_sequence')->get();
                $runningHc = 1000;
                foreach ($periods as $period) {
                    $hires = ($type === WorkforceScenarioType::HIRING_FREEZE->value) ? 0 : (int) round(10 * $headcountMultiplier);
                    $exits = (int) round(8 / ($headcountMultiplier > 0 ? $headcountMultiplier : 1));
                    $closing = max(0, $runningHc + $hires - $exits);

                    HcmWorkforceScenarioHeadcount::create([
                        'tenant_id' => $scenario->tenant_id,
                        'scenario_id' => $scenario->id,
                        'period_name' => $period->period_name,
                        'projected_headcount' => $closing,
                        'projected_fte' => (float) $closing,
                        'projected_hires' => $hires,
                        'projected_exits' => $exits,
                    ]);

                    $runningHc = $closing;
                    $totalProjectedHires += $hires;
                    $totalProjectedExits += $exits;
                }
                $totalProjectedHeadcount = $runningHc;
            } else {
                foreach ($baseHeadcounts as $bh) {
                    $pName = $bh->period->period_name ?? 'Period';
                    $hires = ($type === WorkforceScenarioType::HIRING_FREEZE->value) ? 0 : (int) round($bh->planned_hires * $headcountMultiplier);
                    $exits = $bh->planned_exits;
                    $projectedHc = (int) round($bh->closing_headcount * $headcountMultiplier);

                    HcmWorkforceScenarioHeadcount::create([
                        'tenant_id' => $scenario->tenant_id,
                        'scenario_id' => $scenario->id,
                        'period_name' => $pName,
                        'projected_headcount' => $projectedHc,
                        'projected_fte' => (float) $projectedHc,
                        'projected_hires' => $hires,
                        'projected_exits' => $exits,
                    ]);

                    $totalProjectedHeadcount = $projectedHc;
                    $totalProjectedHires += $hires;
                    $totalProjectedExits += $exits;
                }
            }

            // 2. Calculate Scenario Costs
            HcmWorkforceScenarioCost::where('scenario_id', $scenario->id)->delete();
            $baseCostPlans = $basePlan->costPlans()->get();
            $totalProjectedCost = 0;
            $baseTotalCost = (float) $baseCostPlans->sum('budgeted_amount');

            if ($baseCostPlans->isEmpty()) {
                $defaultCategories = ['base_salary' => 5000000.00, 'benefits' => 900000.00, 'taxes' => 425000.00, 'recruitment' => 120000.00];
                foreach ($defaultCategories as $cat => $baseAmt) {
                    $projCost = round($baseAmt * $costMultiplier, 2);
                    $delta = round($projCost - $baseAmt, 2);

                    HcmWorkforceScenarioCost::create([
                        'tenant_id' => $scenario->tenant_id,
                        'scenario_id' => $scenario->id,
                        'cost_category' => $cat,
                        'projected_cost' => $projCost,
                        'variance_vs_base' => $delta,
                    ]);
                    $totalProjectedCost += $projCost;
                }
            } else {
                foreach ($baseCostPlans as $cp) {
                    $projCost = round($cp->budgeted_amount * $costMultiplier, 2);
                    $delta = round($projCost - $cp->budgeted_amount, 2);

                    HcmWorkforceScenarioCost::create([
                        'tenant_id' => $scenario->tenant_id,
                        'scenario_id' => $scenario->id,
                        'cost_category' => $cp->cost_category,
                        'projected_cost' => $projCost,
                        'variance_vs_base' => $delta,
                    ]);
                    $totalProjectedCost += $projCost;
                }
            }

            $scenario->update(['status' => 'calculated']);

            return [
                'scenario_id' => $scenario->id,
                'scenario_name' => $scenario->name,
                'scenario_type' => $scenario->scenario_type,
                'projected_closing_headcount' => $totalProjectedHeadcount,
                'projected_hires' => $totalProjectedHires,
                'projected_exits' => $totalProjectedExits,
                'total_projected_cost' => round($totalProjectedCost, 2),
                'cost_variance_vs_base' => round($totalProjectedCost - $baseTotalCost, 2),
            ];
        });
    }

    public function compareScenarios(array $scenarioIds): array
    {
        $scenarios = HcmWorkforceScenario::whereIn('id', $scenarioIds)->with(['headcounts', 'costs'])->get();

        $matrix = [];
        foreach ($scenarios as $s) {
            $latestHc = $s->headcounts->last();
            $totalCost = (float) $s->costs->sum('projected_cost');
            $costVariance = (float) $s->costs->sum('variance_vs_base');

            $matrix[] = [
                'scenario_id' => $s->id,
                'name' => $s->name,
                'type' => $s->scenario_type,
                'projected_headcount' => $latestHc->projected_headcount ?? 0,
                'projected_fte' => $latestHc->projected_fte ?? 0.00,
                'projected_hires' => (int) $s->headcounts->sum('projected_hires'),
                'projected_exits' => (int) $s->headcounts->sum('projected_exits'),
                'total_projected_cost' => round($totalCost, 2),
                'cost_variance_vs_base' => round($costVariance, 2),
            ];
        }

        return $matrix;
    }
}
