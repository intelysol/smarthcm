<?php

namespace App\Domains\Analytics\Services;

use App\Domains\Analytics\Models\HcmAnalyticsDashboardDefinition;
use App\Domains\Employee\Models\Employee;
use App\Models\User;

class HcmDashboardService
{
    public function __construct(
        protected HcmWorkforceAnalyticsService $workforceService,
        protected HcmTimeAndAttendanceAnalyticsService $attendanceService,
        protected HcmCompensationAndPayrollAnalyticsService $payrollService,
        protected HcmTalentAndRecruitmentAnalyticsService $talentService,
        protected HcmEngagementAndErAnalyticsService $engagementService
    ) {}

    public function getChroDashboard(string $tenantId): array
    {
        $now = now()->toDateString();
        $startOfMonth = now()->startOfMonth()->toDateString();

        $headcount = $this->workforceService->getHeadcountSummary($tenantId, $now);
        $turnover = $this->workforceService->getTurnoverAnalytics($tenantId, now()->startOfYear()->toDateString(), $now);
        $attendance = $this->attendanceService->getAttendanceMetrics($tenantId, $startOfMonth, $now);
        $payroll = $this->payrollService->getPayrollSummary($tenantId);
        $recruitment = $this->talentService->getRecruitmentFunnel($tenantId, $startOfMonth, $now);
        $talent = $this->talentService->getTalentAndSuccessionSummary($tenantId);
        $engagement = $this->engagementService->getEngagementScoreWithPrivacy($tenantId);
        $er = $this->engagementService->getErCaseAggregates($tenantId);

        return [
            'dashboard_title' => 'CHRO Executive Overview',
            'as_of_date' => $now,
            'headcount' => $headcount,
            'turnover' => $turnover,
            'attendance' => $attendance,
            'payroll' => $payroll,
            'recruitment' => $recruitment,
            'talent' => $talent,
            'engagement' => $engagement,
            'employee_relations' => $er,
        ];
    }

    public function getManagerDashboard(string $tenantId, Employee $manager): array
    {
        $now = now()->toDateString();
        $startOfMonth = now()->startOfMonth()->toDateString();

        $teamHeadcount = $this->workforceService->getHeadcountSummary($tenantId, $now, ['manager_id' => $manager->id]);
        $teamAttendance = $this->attendanceService->getAttendanceMetrics($tenantId, $startOfMonth, $now);

        return [
            'dashboard_title' => 'Manager Team Analytics',
            'manager_name' => "{$manager->first_name} {$manager->last_name}",
            'team_headcount' => $teamHeadcount,
            'team_attendance' => $teamAttendance,
        ];
    }
}
