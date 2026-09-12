<?php

namespace App\Domains\WorkforceOptimization\Services;

use App\Domains\WorkforceOptimization\DTOs\WorkforceOpportunityData;
use App\Domains\WorkforceOptimization\Enums\OpportunityCategory;
use App\Domains\WorkforceOptimization\Models\HcmWorkforceOptimizationOpportunity;
use App\Domains\Organization\Models\Department;
use App\Domains\Employee\Models\Employee;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WorkforceOpportunityService
{
    /**
     * Detect workforce opportunities across all categories.
     */
    public function detectOpportunities(?string $tenantId = null, array $filters = []): array
    {
        $opportunities = [];

        // 1. Capacity Gap and Surplus Detection
        $capOpps = $this->detectCapacityOpportunities($tenantId, $filters);
        $opportunities = array_merge($opportunities, $capOpps);

        // 2. Overtime Spikes
        $otOpps = $this->detectOvertimeAnomalies($tenantId, $filters);
        $opportunities = array_merge($opportunities, $otOpps);

        // 3. Critical Vacancies / Hiring Drag
        $vacOpps = $this->detectCriticalVacancies($tenantId, $filters);
        $opportunities = array_merge($opportunities, $vacOpps);

        // 4. Productivity Bottlenecks
        $prodOpps = $this->detectProductivityBottlenecks($tenantId, $filters);
        $opportunities = array_merge($opportunities, $prodOpps);

        $runId = $filters['run_id'] ?? null;
        $persistedOpps = [];
        foreach ($opportunities as $oppData) {
            $attributes = [
                'category' => $oppData->category,
                'title' => $oppData->title,
                'severity' => strtolower($oppData->severity),
                'department_id' => $oppData->departmentId,
                'role_or_skill' => $oppData->roleOrSkill ?? null,
                'estimated_impact_amount' => $oppData->estimatedImpactAmount ?? 0,
                'capacity_gap_hours' => $oppData->capacityGapHours ?? 0,
                'confidence_score' => $oppData->confidenceScore ?? 0.85,
                'details' => $oppData->details ?? [],
                'status' => 'open',
            ];
            if ($runId) {
                $attributes['run_id'] = $runId;
            }

            $opp = HcmWorkforceOptimizationOpportunity::updateOrCreate(
                [
                    'tenant_id' => $tenantId,
                    'opportunity_code' => $oppData->opportunityCode,
                ],
                $attributes
            );
            $persistedOpps[] = $opp;
        }

        return $persistedOpps;
    }

    /**
     * Detect capacity deficits and underutilized capacity surplus.
     */
    public function detectCapacityOpportunities(?string $tenantId = null, array $filters = []): array
    {
        $opps = [];

        if (Schema::hasTable('hcm_workforce_capacity_plans')) {
            $query = DB::table('hcm_workforce_capacity_plans');
            if (Schema::hasTable('hcm_workforce_plans')) {
                $query->leftJoin('hcm_workforce_plans', 'hcm_workforce_capacity_plans.plan_id', '=', 'hcm_workforce_plans.id')
                    ->select(
                        'hcm_workforce_capacity_plans.*',
                        'hcm_workforce_plans.code as plan_code',
                        'hcm_workforce_plans.department_id as plan_department_id',
                        'hcm_workforce_plans.business_unit_id as plan_business_unit_id'
                    );
            }
            if ($tenantId) {
                $query->where('hcm_workforce_capacity_plans.tenant_id', $tenantId);
            }
            $plans = $query->limit(50)->get();

            foreach ($plans as $plan) {
                $deptId = $plan->plan_department_id ?? $plan->department_id ?? null;
                $empCount = $deptId ? Employee::where('department_id', $deptId)->count() : 0;

                $reqFte = (float) ($plan->calculated_required_fte ?? 0);
                $requiredHours = (float) ($plan->required_capacity_hours ?? $plan->demand_hours ?? ($reqFte > 0 ? $reqFte * 160.0 : 0));
                $availableHours = (float) ($plan->available_capacity_hours ?? $plan->supply_hours ?? ($empCount * 160.0));
                $gap = $requiredHours - $availableHours;
                $planCode = $plan->plan_code ?? $plan->code ?? ('PLAN_'.$plan->id);

                if ($gap > 40) { // Capacity Shortage
                    $opps[] = new WorkforceOpportunityData(
                        opportunityCode: 'CAP_GAP_PLAN_'.$plan->id,
                        category: OpportunityCategory::CAPACITY_GAP->value,
                        title: 'Workforce Capacity Deficit in Department',
                        description: "Capacity plan {$planCode} exhibits a deficit of {$gap} labor hours.",
                        departmentId: $deptId ? (string) $deptId : null,
                        businessUnitId: isset($plan->plan_business_unit_id) ? (string) $plan->plan_business_unit_id : (isset($plan->business_unit_id) ? (string) $plan->business_unit_id : null),
                        severity: $gap > 160 ? 'CRITICAL' : 'HIGH',
                        estimatedHoursGap: $gap,
                        estimatedCostImpact: $gap * 45.0,
                        estimatedProductivityImpactPct: -12.0,
                        detectionMetrics: ['required_hours' => $requiredHours, 'available_hours' => $availableHours, 'gap' => $gap]
                    );
                } elseif ($gap < -40) { // Capacity Surplus
                    $surplus = abs($gap);
                    $opps[] = new WorkforceOpportunityData(
                        opportunityCode: 'CAP_SURPLUS_PLAN_'.$plan->id,
                        category: OpportunityCategory::CAPACITY_SURPLUS->value,
                        title: 'Underutilized Workforce Capacity',
                        description: "Capacity plan {$planCode} has {$surplus} surplus hours available for redeployment.",
                        departmentId: $deptId ? (string) $deptId : null,
                        businessUnitId: isset($plan->plan_business_unit_id) ? (string) $plan->plan_business_unit_id : (isset($plan->business_unit_id) ? (string) $plan->business_unit_id : null),
                        severity: 'MEDIUM',
                        estimatedHoursGap: -$surplus,
                        estimatedCostImpact: -$surplus * 25.0,
                        estimatedProductivityImpactPct: 0.0,
                        detectionMetrics: ['surplus_hours' => $surplus]
                    );
                }
            }
        }

        // Fallback: If no capacity plans, inspect Departments directly
        if (empty($opps)) {
            $deptQuery = Department::query();
            if ($tenantId && Schema::hasColumn('departments', 'tenant_id')) {
                $deptQuery->where('tenant_id', $tenantId);
            }
            $departments = $deptQuery->take(10)->get();

            foreach ($departments as $dept) {
                $empCount = Employee::where('department_id', $dept->id)->count();
                if ($empCount < 3) {
                    $opps[] = new WorkforceOpportunityData(
                        opportunityCode: 'CAP_GAP_DEPT_'.$dept->id,
                        category: OpportunityCategory::CAPACITY_GAP->value,
                        title: "Low Staffing Capacity in {$dept->name}",
                        description: "Department {$dept->name} has fewer than 3 employees, representing operational bottleneck risk.",
                        departmentId: (string) $dept->id,
                        businessUnitId: (string) ($dept->business_unit_id ?? null),
                        severity: 'HIGH',
                        estimatedHoursGap: 80.0,
                        estimatedCostImpact: 3600.0,
                        estimatedProductivityImpactPct: -15.0,
                        detectionMetrics: ['headcount' => $empCount]
                    );
                }
            }
        }

        return $opps;
    }

    /**
     * Detect overtime spikes and burnout risks.
     */
    public function detectOvertimeAnomalies(?string $tenantId = null, array $filters = []): array
    {
        $opps = [];

        // Check Epic 2.50 productivity measurements
        if (Schema::hasTable('hcm_productivity_measurements')) {
            $query = DB::table('hcm_productivity_measurements')
                ->where('overtime_hours', '>', 50);

            if ($tenantId) {
                $query->where('tenant_id', $tenantId);
            }

            $highOtDepts = $query->limit(20)->get();
            foreach ($highOtDepts as $row) {
                $period = $row->period_start ?? $row->period_name ?? 'current';
                $opps[] = new WorkforceOpportunityData(
                    opportunityCode: 'OT_ANOMALY_DEPT_'.$row->department_id.'_PER_'.$period,
                    category: OpportunityCategory::OVERTIME_ANOMALY->value,
                    title: 'Severe Overtime Strain Detected',
                    description: "High overtime accumulation of {$row->overtime_hours} hours detected in period {$period}.",
                    departmentId: (string) $row->department_id,
                    severity: $row->overtime_hours > 100 ? 'HIGH' : 'MEDIUM',
                    estimatedHoursGap: (float) $row->overtime_hours,
                    estimatedCostImpact: (float) ($row->overtime_hours * 55.0),
                    estimatedProductivityImpactPct: -8.5,
                    detectionMetrics: ['overtime_hours' => $row->overtime_hours]
                );
            }
        }

        // Check legacy or alternate table if present
        if (Schema::hasTable('hcm_department_productivity_aggregates')) {
            $query = DB::table('hcm_department_productivity_aggregates')
                ->where('overtime_hours', '>', 50);

            if ($tenantId) {
                $query->where('tenant_id', $tenantId);
            }

            $highOtDepts = $query->limit(20)->get();
            foreach ($highOtDepts as $row) {
                $opps[] = new WorkforceOpportunityData(
                    opportunityCode: 'OT_ANOMALY_DEPT_'.$row->department_id.'_PER_'.$row->period_start,
                    category: OpportunityCategory::OVERTIME_ANOMALY->value,
                    title: 'Severe Overtime Strain Detected',
                    description: "High overtime accumulation of {$row->overtime_hours} hours detected in period {$row->period_start}.",
                    departmentId: (string) $row->department_id,
                    severity: $row->overtime_hours > 100 ? 'HIGH' : 'MEDIUM',
                    estimatedHoursGap: (float) $row->overtime_hours,
                    estimatedCostImpact: (float) ($row->overtime_hours * 55.0),
                    estimatedProductivityImpactPct: -8.5,
                    detectionMetrics: ['overtime_hours' => $row->overtime_hours]
                );
            }
        }

        return $opps;
    }

    /**
     * Detect critical vacancies impacting operations.
     */
    public function detectCriticalVacancies(?string $tenantId = null, array $filters = []): array
    {
        $opps = [];

        if (Schema::hasTable('hcm_job_requisitions')) {
            $query = DB::table('hcm_job_requisitions')
                ->where('status', 'OPEN');

            if ($tenantId) {
                $query->where('tenant_id', $tenantId);
            }

            $vacancies = $query->limit(15)->get();
            foreach ($vacancies as $vac) {
                $opps[] = new WorkforceOpportunityData(
                    opportunityCode: 'CRIT_VAC_'.$vac->id,
                    category: OpportunityCategory::CRITICAL_VACANCY->value,
                    title: "Critical Vacancy: {$vac->title}",
                    description: "Open requisition {$vac->requisition_number} creating sustained capacity drag.",
                    departmentId: $vac->department_id ?? null,
                    jobRoleId: $vac->job_role_id ?? null,
                    severity: 'HIGH',
                    estimatedHoursGap: 160.0,
                    estimatedCostImpact: 7200.0,
                    estimatedProductivityImpactPct: -10.0,
                    detectionMetrics: ['requisition_id' => $vac->id, 'open_days' => 30]
                );
            }
        }

        return $opps;
    }

    /**
     * Detect productivity bottlenecks from Epic 2.50.
     */
    public function detectProductivityBottlenecks(?string $tenantId = null, array $filters = []): array
    {
        $opps = [];

        if (Schema::hasTable('hcm_productivity_bottlenecks')) {
            $query = DB::table('hcm_productivity_bottlenecks')
                ->where('status', 'OPEN');

            if ($tenantId) {
                $query->where('tenant_id', $tenantId);
            }

            $bottlenecks = $query->limit(20)->get();
            foreach ($bottlenecks as $btn) {
                $opps[] = new WorkforceOpportunityData(
                    opportunityCode: 'PROD_BTN_'.$btn->id,
                    category: OpportunityCategory::PRODUCTIVITY_BOTTLENECK->value,
                    title: $btn->bottleneck_type ?? 'Productivity Bottleneck',
                    description: $btn->description ?? 'Identified bottleneck limiting throughput.',
                    departmentId: $btn->department_id ?? null,
                    severity: $btn->severity ?? 'HIGH',
                    estimatedHoursGap: 40.0,
                    estimatedCostImpact: 2000.0,
                    estimatedProductivityImpactPct: -14.0,
                    detectionMetrics: ['bottleneck_id' => $btn->id, 'root_cause' => $btn->root_cause_type ?? null]
                );
            }
        }

        return $opps;
    }
}
