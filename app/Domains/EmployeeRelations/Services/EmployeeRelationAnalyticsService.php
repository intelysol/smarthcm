<?php

namespace App\Domains\EmployeeRelations\Services;

use App\Domains\Analytics\Services\AnalyticsService;
use App\Domains\EmployeeRelations\Enums\CaseStatus;
use App\Domains\EmployeeRelations\Models\EmployeeRelationAppeal;
use App\Domains\EmployeeRelations\Models\EmployeeRelationCase;
use App\Domains\EmployeeRelations\Models\EmployeeRelationCaseSla;
use App\Domains\EmployeeRelations\Models\EmployeeRelationCorrectiveAction;
use App\Domains\EmployeeRelations\Models\EmployeeRelationInvestigation;
use Illuminate\Support\Carbon;

class EmployeeRelationAnalyticsService
{
    public function __construct(
        protected ?AnalyticsService $analyticsService = null
    ) {}

    /**
     * Generate comprehensive ER analytics metrics
     */
    public function generateMetrics(string $tenantId, ?string $from = null, ?string $to = null): array
    {
        $fromDate = $from ? Carbon::parse($from) : now()->subYear();
        $toDate = $to ? Carbon::parse($to) : now();

        $casesQuery = EmployeeRelationCase::query()
            ->where('employee_relation_cases.tenant_id', $tenantId)
            ->whereBetween('employee_relation_cases.opened_at', [$fromDate, $toDate]);

        $totalCases = $casesQuery->count();
        $openCases = (clone $casesQuery)->whereNotIn('employee_relation_cases.status', [CaseStatus::CLOSED->value, CaseStatus::ARCHIVED->value, CaseStatus::CANCELLED->value, CaseStatus::REJECTED->value])->count();
        $closedCases = (clone $casesQuery)->where('employee_relation_cases.status', CaseStatus::CLOSED->value)->count();

        // Average resolution time (in days)
        $closedWithDuration = (clone $casesQuery)
            ->whereNotNull('employee_relation_cases.closed_at')
            ->get();

        $avgResolutionDays = $closedWithDuration->isNotEmpty()
            ? round($closedWithDuration->avg(fn ($c) => $c->opened_at->diffInDays($c->closed_at)), 1)
            : 0.0;

        // SLA Compliance
        $slasQuery = EmployeeRelationCaseSla::query()
            ->where('employee_relation_case_slas.tenant_id', $tenantId)
            ->whereBetween('employee_relation_case_slas.created_at', [$fromDate, $toDate]);

        $totalSlas = $slasQuery->count();
        $metSlas = (clone $slasQuery)->where('employee_relation_case_slas.status', 'met')->count();
        $slaComplianceRate = $totalSlas > 0 ? round(($metSlas / $totalSlas) * 100, 1) : 100.0;

        // Investigation Completion Rate
        $invsQuery = EmployeeRelationInvestigation::query()
            ->where('employee_relation_investigations.tenant_id', $tenantId)
            ->whereBetween('employee_relation_investigations.created_at', [$fromDate, $toDate]);

        $totalInvs = $invsQuery->count();
        $completedInvs = (clone $invsQuery)->where('employee_relation_investigations.status', 'completed')->count();
        $invCompletionRate = $totalInvs > 0 ? round(($completedInvs / $totalInvs) * 100, 1) : 0.0;

        // Appeal Rate
        $appealsQuery = EmployeeRelationAppeal::query()
            ->where('employee_relation_appeals.tenant_id', $tenantId)
            ->whereBetween('employee_relation_appeals.submitted_at', [$fromDate, $toDate]);

        $totalAppeals = $appealsQuery->count();
        $appealRate = $closedCases > 0 ? round(($totalAppeals / $closedCases) * 100, 1) : 0.0;

        // Corrective Actions Completion Rate
        $actionsQuery = EmployeeRelationCorrectiveAction::query()
            ->where('employee_relation_corrective_actions.tenant_id', $tenantId)
            ->whereBetween('employee_relation_corrective_actions.created_at', [$fromDate, $toDate]);

        $totalActions = $actionsQuery->count();
        $completedActions = (clone $actionsQuery)->whereIn('employee_relation_corrective_actions.status', ['completed', 'verified'])->count();
        $actionCompletionRate = $totalActions > 0 ? round(($completedActions / $totalActions) * 100, 1) : 0.0;

        // Breakdown by Case Type
        $byType = (clone $casesQuery)
            ->join('employee_relation_case_types', 'employee_relation_case_types.id', '=', 'employee_relation_cases.case_type_id')
            ->selectRaw('employee_relation_case_types.name as case_type, count(*) as count')
            ->groupBy('employee_relation_case_types.name')
            ->pluck('count', 'case_type')
            ->all();

        // Breakdown by Severity
        $bySeverity = (clone $casesQuery)
            ->selectRaw('employee_relation_cases.severity, count(*) as count')
            ->groupBy('employee_relation_cases.severity')
            ->pluck('count', 'severity')
            ->all();

        // Ingest into analytics platform if available
        if ($this->analyticsService) {
            $facts = [
                ['fact_type' => 'er_case_volume', 'fact_date' => now()->toDateString(), 'measures' => ['value' => $totalCases]],
                ['fact_type' => 'er_resolution_days', 'fact_date' => now()->toDateString(), 'measures' => ['value' => $avgResolutionDays]],
                ['fact_type' => 'er_sla_compliance', 'fact_date' => now()->toDateString(), 'measures' => ['value' => $slaComplianceRate]],
                ['fact_type' => 'er_investigation_rate', 'fact_date' => now()->toDateString(), 'measures' => ['value' => $invCompletionRate]],
            ];
            $this->analyticsService->ingest($tenantId, $facts);
        }

        return [
            'total_cases' => $totalCases,
            'open_cases' => $openCases,
            'closed_cases' => $closedCases,
            'average_resolution_days' => $avgResolutionDays,
            'sla_compliance_rate' => $slaComplianceRate,
            'investigation_completion_rate' => $invCompletionRate,
            'appeal_rate' => $appealRate,
            'corrective_action_completion_rate' => $actionCompletionRate,
            'by_type' => $byType,
            'by_severity' => $bySeverity,
        ];
    }
}
