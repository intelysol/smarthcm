<?php

namespace Database\Seeders;

use App\Domains\Shared\Models\Tenant;
use App\Domains\WorkforcePlanning\Models\HcmWorkforcePlan;
use App\Domains\WorkforcePlanning\Models\HcmWorkforcePlanAssumption;
use App\Domains\WorkforcePlanning\Models\HcmWorkforcePlanPeriod;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class WorkforcePlanningDefaultDataSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            $plan = HcmWorkforcePlan::firstOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'code' => 'WFP-2027',
                ],
                [
                    'name' => 'FY2027 Enterprise Workforce Plan',
                    'planning_cycle' => 'FY2027',
                    'planning_type' => 'annual',
                    'start_date' => '2027-01-01',
                    'end_date' => '2027-12-31',
                    'status' => 'draft',
                    'current_version' => 1,
                    'currency' => 'USD',
                    'description' => 'Comprehensive strategic workforce plan covering demand, headcount, positions, and labor budgets for FY2027.',
                ]
            );

            // 12 Monthly Periods
            $months = [
                'Jan 2027', 'Feb 2027', 'Mar 2027', 'Apr 2027',
                'May 2027', 'Jun 2027', 'Jul 2027', 'Aug 2027',
                'Sep 2027', 'Oct 2027', 'Nov 2027', 'Dec 2027'
            ];

            foreach ($months as $idx => $mName) {
                $start = Carbon::parse('2027-01-01')->addMonths($idx)->startOfMonth();
                $end = Carbon::parse('2027-01-01')->addMonths($idx)->endOfMonth();

                HcmWorkforcePlanPeriod::firstOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'plan_id' => $plan->id,
                        'period_sequence' => $idx + 1,
                    ],
                    [
                        'period_name' => $mName,
                        'start_date' => $start->toDateString(),
                        'end_date' => $end->toDateString(),
                    ]
                );
            }

            // Standard Assumptions
            $defaultAssumptions = [
                ['key' => 'annual_salary_increase_pct', 'name' => 'Annual Salary Increase Rate', 'type' => 'percentage', 'val' => 6.5],
                ['key' => 'expected_attrition_pct', 'name' => 'Annual Expected Attrition Rate', 'type' => 'percentage', 'val' => 10.0],
                ['key' => 'recruitment_fill_rate_pct', 'name' => 'Recruitment Fill Rate', 'type' => 'percentage', 'val' => 88.0],
                ['key' => 'hiring_lead_time_days', 'name' => 'Average Hiring Lead Time (Days)', 'type' => 'days', 'val' => 45.0],
                ['key' => 'benefit_cost_pct', 'name' => 'Employer Benefit Cost (% of Base)', 'type' => 'percentage', 'val' => 18.0],
                ['key' => 'payroll_tax_pct', 'name' => 'Employer Statutory Tax (% of Base)', 'type' => 'percentage', 'val' => 8.5],
            ];

            foreach ($defaultAssumptions as $as) {
                HcmWorkforcePlanAssumption::firstOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'plan_id' => $plan->id,
                        'assumption_key' => $as['key'],
                    ],
                    [
                        'name' => $as['name'],
                        'data_type' => $as['type'],
                        'value' => $as['val'],
                        'effective_from' => '2027-01-01',
                        'effective_to' => '2027-12-31',
                    ]
                );
            }
        }
    }
}
