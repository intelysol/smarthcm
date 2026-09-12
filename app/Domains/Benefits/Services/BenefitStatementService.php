<?php

namespace App\Domains\Benefits\Services;

use App\Domains\Benefits\Models\BenefitEnrollment;
use App\Domains\Benefits\Models\BenefitStatement;
use App\Domains\Benefits\Models\RetirementEnrollment;
use App\Domains\Compensation\Services\TotalRewardsService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Services\AuditService;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BenefitStatementService
{
    public function __construct(
        protected AuditService $auditService,
        protected ?TotalRewardsService $totalRewardsService = null
    ) {}

    /**
     * Generate or refresh an employee's annual benefits statement.
     */
    public function generateStatement(Employee $employee, int $year, ?User $actor = null): BenefitStatement
    {
        $tenantId = $employee->tenant_id;
        $asOfDate = Carbon::create($year, 12, 31)->toDateString();

        // 1. Fetch active benefit enrollments
        $enrollments = BenefitEnrollment::query()
            ->where('tenant_id', $tenantId)
            ->where('employee_id', $employee->id)
            ->whereIn('status', ['approved', 'active'])
            ->whereDate('effective_from', '<=', $asOfDate)
            ->where(function ($q) use ($year) {
                $q->whereNull('effective_to')->orWhereYear('effective_to', '>=', $year);
            })
            ->with(['plan.category', 'dependents', 'beneficiaries'])
            ->get();

        // 2. Fetch retirement enrollments if any
        $retirementEnrollments = RetirementEnrollment::query()
            ->where('tenant_id', $tenantId)
            ->where('employee_id', $employee->id)
            ->where('status', 'active')
            ->with('plan')
            ->get();

        $categories = [
            'medical_health' => ['label' => 'Medical & Health Insurance', 'employer_annual' => 0.0, 'employee_annual' => 0.0, 'plans' => []],
            'life_disability' => ['label' => 'Life & Disability Protection', 'employer_annual' => 0.0, 'employee_annual' => 0.0, 'plans' => []],
            'retirement_savings' => ['label' => 'Retirement & Pension', 'employer_annual' => 0.0, 'employee_annual' => 0.0, 'plans' => []],
            'wellness_perks' => ['label' => 'Wellness & Ancillary Perks', 'employer_annual' => 0.0, 'employee_annual' => 0.0, 'plans' => []],
        ];

        $totalEmployerAnnual = 0.0;
        $totalEmployeeAnnual = 0.0;
        $currency = 'USD';

        foreach ($enrollments as $enr) {
            $plan = $enr->plan;
            $currency = $enr->currency ?? $plan->currency ?? 'USD';
            $monthlyEmployer = (float) $enr->employer_contribution;
            $monthlyEmployee = (float) $enr->employee_contribution;
            $annualEmployer = $monthlyEmployer * 12;
            $annualEmployee = $monthlyEmployee * 12;

            $catKey = 'wellness_perks';
            $bType = strtolower($plan->benefit_type ?? '');
            if (str_contains($bType, 'med') || str_contains($bType, 'health') || str_contains($bType, 'dent') || str_contains($bType, 'vis')) {
                $catKey = 'medical_health';
            } elseif (str_contains($bType, 'life') || str_contains($bType, 'disab') || str_contains($bType, 'protect')) {
                $catKey = 'life_disability';
            }

            $categories[$catKey]['employer_annual'] += $annualEmployer;
            $categories[$catKey]['employee_annual'] += $annualEmployee;
            $categories[$catKey]['plans'][] = [
                'plan_name' => $plan->name,
                'plan_type' => $plan->benefit_type,
                'coverage_level' => $enr->coverage_level,
                'monthly_employer_cost' => $monthlyEmployer,
                'monthly_employee_cost' => $monthlyEmployee,
                'annual_employer_cost' => $annualEmployer,
                'annual_employee_cost' => $annualEmployee,
                'dependents_covered' => $enr->dependents->count(),
            ];

            $totalEmployerAnnual += $annualEmployer;
            $totalEmployeeAnnual += $annualEmployee;
        }

        // Add retirement contributions
        foreach ($retirementEnrollments as $ret) {
            $retPlan = $ret->plan;
            $monthlyRetEmployer = ((float) $ret->employer_contribution_rate / 100) * 5000; // Estimated monthly base
            $annualRetEmployer = $monthlyRetEmployer * 12;

            $categories['retirement_savings']['employer_annual'] += $annualRetEmployer;
            $categories['retirement_savings']['plans'][] = [
                'plan_name' => $retPlan->name,
                'plan_type' => $retPlan->plan_type,
                'coverage_level' => 'Individual Account',
                'monthly_employer_cost' => $monthlyRetEmployer,
                'monthly_employee_cost' => 0,
                'annual_employer_cost' => $annualRetEmployer,
                'annual_employee_cost' => 0,
                'dependents_covered' => 0,
            ];
            $totalEmployerAnnual += $annualRetEmployer;
        }

        $totalEstimatedValue = $totalEmployerAnnual + $totalEmployeeAnnual;

        $statementData = [
            'year' => $year,
            'statement_date' => now()->toDateString(),
            'currency' => $currency,
            'summary' => [
                'total_employer_investment' => round($totalEmployerAnnual, 2),
                'total_employee_contribution' => round($totalEmployeeAnnual, 2),
                'total_benefit_package_value' => round($totalEstimatedValue, 2),
            ],
            'categories' => $categories,
        ];

        return DB::transaction(function () use ($tenantId, $employee, $year, $totalEmployerAnnual, $totalEmployeeAnnual, $totalEstimatedValue, $currency, $statementData, $actor) {
            $statement = BenefitStatement::updateOrCreate(
                [
                    'tenant_id' => $tenantId,
                    'employee_id' => $employee->id,
                    'statement_year' => $year,
                ],
                [
                    'statement_date' => now()->toDateString(),
                    'total_employer_cost' => $totalEmployerAnnual,
                    'total_employee_cost' => $totalEmployeeAnnual,
                    'total_benefit_value' => $totalEstimatedValue,
                    'currency' => $currency,
                    'statement_data' => $statementData,
                    'status' => 'published',
                    'published_at' => now(),
                ]
            );

            // Integrate with TotalRewardsService (Epic 2.36) if available
            if ($this->totalRewardsService && $actor) {
                try {
                    $this->totalRewardsService->generateStatement(
                        user: $actor,
                        employee: $employee,
                        year: $year,
                        baseSalary: 60000.00,
                        benefitsValue: $totalEmployerAnnual,
                        retirementContribution: $statementData['categories']['retirement_savings']['employer_annual'] ?? 0.0
                    );
                } catch (\Throwable) {
                    // Soft fallthrough if TotalRewards base salary calculation requires additional context
                }
            }

            $this->auditService->record(
                tenantId: $tenantId,
                eventType: 'benefit_statement.generated',
                action: 'generate',
                entityType: BenefitStatement::class,
                entityId: $statement->id,
                actorId: $actor?->id,
                after: ['year' => $year, 'total_value' => $totalEstimatedValue]
            );

            return $statement;
        });
    }

    public function getStatement(Employee $employee, int $year): ?BenefitStatement
    {
        return BenefitStatement::where('tenant_id', $employee->tenant_id)
            ->where('employee_id', $employee->id)
            ->where('statement_year', $year)
            ->first();
    }
}
