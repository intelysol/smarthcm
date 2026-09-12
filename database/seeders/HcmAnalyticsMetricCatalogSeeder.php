<?php

namespace Database\Seeders;

use App\Domains\Analytics\Models\HcmAnalyticsDashboardDefinition;
use App\Domains\Analytics\Models\HcmAnalyticsDataProduct;
use App\Domains\Analytics\Models\HcmAnalyticsDataQualityCheck;
use App\Domains\Analytics\Models\HcmAnalyticsMetric;
use App\Domains\Analytics\Models\HcmAnalyticsMetricVersion;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Seeder;

class HcmAnalyticsMetricCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = Tenant::all();
        foreach ($tenants as $tenant) {
            $this->seedTenantData($tenant->id);
        }
    }

    public function seedTenantData(string $tenantId): void
    {
        // 1. Seed Data Products
        $dataProducts = [
            ['code' => 'DP_WORKFORCE', 'name' => 'Workforce & Headcount', 'domain_module' => 'core_hr', 'description' => 'Employee active counts, FTE, movements, terminations, promotions'],
            ['code' => 'DP_RECRUITMENT', 'name' => 'Recruitment & Hiring Funnel', 'domain_module' => 'recruitment', 'description' => 'Requisitions, candidate stages, interview pipelines, time to hire'],
            ['code' => 'DP_ATTENDANCE', 'name' => 'Attendance & Time Records', 'domain_module' => 'attendance', 'description' => 'Shifts, actual hours, overtime, punctuality, missing punches'],
            ['code' => 'DP_LEAVE', 'name' => 'Leave & Absence Utilization', 'domain_module' => 'leave', 'description' => 'Leave days taken, balances, absence rates by leave category'],
            ['code' => 'DP_PAYROLL', 'name' => 'Payroll & Compensation Costs', 'domain_module' => 'payroll', 'description' => 'Gross payroll, net pay, taxes, allowances, employer statutory contributions'],
            ['code' => 'DP_EXPENSES', 'name' => 'Employee Expenses & Travel', 'domain_module' => 'expenses', 'description' => 'Claims submitted, categories, travel spend, policy exceptions'],
            ['code' => 'DP_PERFORMANCE', 'name' => 'Performance & Goals', 'domain_module' => 'performance', 'description' => 'Review completion %, rating distributions, goal achievement %'],
            ['code' => 'DP_TALENT', 'name' => 'Talent & Succession Coverage', 'domain_module' => 'career', 'description' => 'Talent pool bench strength, critical positions, succession readiness'],
            ['code' => 'DP_ENGAGEMENT', 'name' => 'Employee Engagement & Surveys', 'domain_module' => 'engagement', 'description' => 'Survey participation %, eNPS, favorability with privacy suppression'],
            ['code' => 'DP_ER', 'name' => 'Employee Relations Aggregates', 'domain_module' => 'employee_relations', 'description' => 'Case aging, status breakdown, department-level volume trends'],
            ['code' => 'DP_HR_SERVICE', 'name' => 'HR Service Delivery & Helpdesk', 'domain_module' => 'self_service', 'description' => 'Ticket volumes, SLA resolution %, average handling duration'],
        ];

        foreach ($dataProducts as $dp) {
            HcmAnalyticsDataProduct::updateOrCreate(
                ['tenant_id' => $tenantId, 'code' => $dp['code']],
                array_merge($dp, ['tenant_id' => $tenantId, 'is_active' => true])
            );
        }

        // 2. Seed Standard HCM Metrics
        $metrics = [
            // Workforce
            ['code' => 'HCM_HEADCOUNT_TOTAL', 'name' => 'Total Headcount', 'category' => 'workforce', 'unit' => 'count', 'aggregation' => 'count', 'sensitivity' => 'public', 'formula' => 'COUNT(all_employees)'],
            ['code' => 'HCM_HEADCOUNT_ACTIVE', 'name' => 'Active Headcount', 'category' => 'workforce', 'unit' => 'count', 'aggregation' => 'count', 'sensitivity' => 'public', 'formula' => 'COUNT(status = active)'],
            ['code' => 'HCM_FTE_TOTAL', 'name' => 'Full-Time Equivalent (FTE)', 'category' => 'workforce', 'unit' => 'count', 'aggregation' => 'sum', 'sensitivity' => 'public', 'formula' => 'SUM(fte_weight)'],
            ['code' => 'HCM_TURNOVER_RATE', 'name' => 'Turnover Rate', 'category' => 'turnover', 'unit' => 'percentage', 'aggregation' => 'avg', 'sensitivity' => 'public', 'formula' => '(Total Exits / Average Headcount) * 100'],
            ['code' => 'HCM_VOLUNTARY_TURNOVER', 'name' => 'Voluntary Turnover Rate', 'category' => 'turnover', 'unit' => 'percentage', 'aggregation' => 'avg', 'sensitivity' => 'public', 'formula' => '(Voluntary Exits / Average Headcount) * 100'],
            
            // Recruitment
            ['code' => 'HCM_TIME_TO_HIRE', 'name' => 'Average Time to Hire', 'category' => 'recruitment', 'unit' => 'days', 'aggregation' => 'avg', 'sensitivity' => 'public', 'formula' => 'AVG(offer_accepted_date - requisition_opened_date)'],
            ['code' => 'HCM_OFFER_ACCEPTANCE_RATE', 'name' => 'Offer Acceptance Rate', 'category' => 'recruitment', 'unit' => 'percentage', 'aggregation' => 'avg', 'sensitivity' => 'public', 'formula' => '(Accepted Offers / Total Offers Extended) * 100'],
            
            // Attendance & Leave
            ['code' => 'HCM_ATTENDANCE_RATE', 'name' => 'Attendance Rate', 'category' => 'attendance', 'unit' => 'percentage', 'aggregation' => 'avg', 'sensitivity' => 'public', 'formula' => '(Present Days / Scheduled Work Days) * 100'],
            ['code' => 'HCM_ABSENTEEISM_RATE', 'name' => 'Absenteeism Rate', 'category' => 'attendance', 'unit' => 'percentage', 'aggregation' => 'avg', 'sensitivity' => 'public', 'formula' => '(Unplanned Absent Days / Scheduled Work Days) * 100'],
            ['code' => 'HCM_OVERTIME_HOURS', 'name' => 'Total Overtime Hours', 'category' => 'attendance', 'unit' => 'hours', 'aggregation' => 'sum', 'sensitivity' => 'public', 'formula' => 'SUM(approved_overtime_hours)'],
            ['code' => 'HCM_LEAVE_UTILIZATION', 'name' => 'Leave Utilization Rate', 'category' => 'leave', 'unit' => 'percentage', 'aggregation' => 'avg', 'sensitivity' => 'public', 'formula' => '(Leave Days Taken / Entitled Leave Days) * 100'],

            // Payroll & Expenses
            ['code' => 'HCM_GROSS_PAYROLL_TOTAL', 'name' => 'Gross Payroll Cost', 'category' => 'payroll', 'unit' => 'currency', 'aggregation' => 'sum', 'sensitivity' => 'sensitive', 'formula' => 'SUM(gross_earnings)'],
            ['code' => 'HCM_AVG_SALARY', 'name' => 'Average Employee Salary', 'category' => 'compensation', 'unit' => 'currency', 'aggregation' => 'avg', 'sensitivity' => 'sensitive', 'formula' => 'AVG(base_salary)'],
            ['code' => 'HCM_EXPENSE_CLAIM_TOTAL', 'name' => 'Reimbursed Expenses', 'category' => 'expenses', 'unit' => 'currency', 'aggregation' => 'sum', 'sensitivity' => 'public', 'formula' => 'SUM(approved_reimbursement_amount)'],

            // Performance & Engagement
            ['code' => 'HCM_PERF_REVIEW_COMPLETION', 'name' => 'Review Completion Rate', 'category' => 'performance', 'unit' => 'percentage', 'aggregation' => 'avg', 'sensitivity' => 'public', 'formula' => '(Completed Reviews / Total Assigned Reviews) * 100'],
            ['code' => 'HCM_ENPS_SCORE', 'name' => 'Employee Net Promoter Score (eNPS)', 'category' => 'engagement', 'unit' => 'score', 'aggregation' => 'avg', 'sensitivity' => 'sensitive', 'formula' => '% Promoters - % Detractors'],
            ['code' => 'HCM_HR_SERVICE_SLA_COMPLIANCE', 'name' => 'Service Desk SLA Compliance', 'category' => 'hr_service', 'unit' => 'percentage', 'aggregation' => 'avg', 'sensitivity' => 'public', 'formula' => '(Tickets Resolved Within SLA / Total Resolved Tickets) * 100'],
        ];

        foreach ($metrics as $m) {
            $metricModel = HcmAnalyticsMetric::updateOrCreate(
                ['tenant_id' => $tenantId, 'code' => $m['code']],
                array_merge($m, ['tenant_id' => $tenantId, 'current_version' => 1, 'is_active' => true])
            );

            HcmAnalyticsMetricVersion::updateOrCreate(
                ['tenant_id' => $tenantId, 'hcm_analytics_metric_id' => $metricModel->id, 'version_number' => 1],
                [
                    'tenant_id' => $tenantId,
                    'effective_from' => '2026-01-01',
                    'calculation_definition' => ['formula' => $m['formula'], 'aggregation' => $m['aggregation']],
                    'change_summary' => 'Initial standard HCM metric definition',
                ]
            );
        }

        // 3. Seed Standard Dashboard Definitions
        $dashboards = [
            ['code' => 'DASH_CHRO', 'name' => 'CHRO Executive Dashboard', 'dashboard_type' => 'chro', 'description' => 'Comprehensive executive overview of workforce, turnover, payroll, talent, and engagement'],
            ['code' => 'DASH_HROPS', 'name' => 'HR Operations Dashboard', 'dashboard_type' => 'hr_ops', 'description' => 'Operational metrics covering onboarding, attendance, leave, and service desk SLA'],
            ['code' => 'DASH_MANAGER', 'name' => 'Manager Team Analytics', 'dashboard_type' => 'manager', 'description' => 'Scoped team metrics for line managers: team headcount, attendance, leaves, and reviews'],
            ['code' => 'DASH_PAYROLL', 'name' => 'Payroll & Compensation Dashboard', 'dashboard_type' => 'payroll', 'description' => 'Confidential payroll trends, overtime costs, benefits, and salary distributions'],
            ['code' => 'DASH_RECRUITMENT', 'name' => 'Talent Acquisition Dashboard', 'dashboard_type' => 'recruitment', 'description' => 'Recruitment funnel, pipeline conversion, offer acceptance, and recruiter workload'],
        ];

        foreach ($dashboards as $d) {
            HcmAnalyticsDashboardDefinition::updateOrCreate(
                ['tenant_id' => $tenantId, 'code' => $d['code']],
                array_merge($d, ['tenant_id' => $tenantId, 'is_system' => true])
            );
        }

        // 4. Seed Data Quality Rules
        $qualityChecks = [
            ['code' => 'DQ_MISSING_DEPT', 'name' => 'Employees Without Department', 'domain_module' => 'core_hr', 'severity' => 'error', 'check_type' => 'missing_department', 'description' => 'Detect active employees lacking assigned department hierarchy'],
            ['code' => 'DQ_MISSING_MGR', 'name' => 'Employees Without Manager', 'domain_module' => 'core_hr', 'severity' => 'warning', 'check_type' => 'missing_manager', 'description' => 'Detect non-executive active employees without designated reporting manager'],
            ['code' => 'DQ_MISSING_SALARY', 'name' => 'Active Employees Without Salary Structure', 'domain_module' => 'payroll', 'severity' => 'error', 'check_type' => 'missing_salary', 'description' => 'Detect active employees without current compensation profile'],
            ['code' => 'DQ_DUPLICATE_EMP', 'name' => 'Duplicate Employee Records', 'domain_module' => 'core_hr', 'severity' => 'error', 'check_type' => 'duplicate_employee', 'description' => 'Detect possible duplicate national ID or official email matches'],
        ];

        foreach ($qualityChecks as $qc) {
            HcmAnalyticsDataQualityCheck::updateOrCreate(
                ['tenant_id' => $tenantId, 'code' => $qc['code']],
                array_merge($qc, ['tenant_id' => $tenantId, 'is_active' => true])
            );
        }
    }
}
