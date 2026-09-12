<?php

namespace App\Domains\WorkforceIntelligence\Services;

use App\Domains\WorkforceIntelligence\Contracts\WorkforceCommandCenterInterface;
use App\Domains\WorkforceIntelligence\DTOs\ExecutiveScorecardData;
use App\Domains\WorkforceIntelligence\DTOs\WorkforceHealthIndexData;
use App\Domains\WorkforceIntelligence\DTOs\WorkforcePulseData;
use App\Domains\WorkforceIntelligence\Models\CommandCenterDashboard;
use App\Domains\WorkforceIntelligence\Models\CommandCenterDecisionItem;
use App\Domains\WorkforceIntelligence\Models\CommandCenterRisk;
use App\Domains\WorkforceIntelligence\Models\CommandCenterSnapshot;
use App\Domains\WorkforceIntelligence\Models\CommandCenterWidget;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WorkforceCommandCenterService implements WorkforceCommandCenterInterface
{
    public function __construct(
        protected KpiOrchestrationService $kpiService,
        protected WorkforceHealthIndexService $healthIndexService,
        protected WorkforcePulseService $pulseService,
        protected WorkforceRiskAggregationService $riskService,
        protected WorkforceAlertService $alertService,
        protected WorkforceDecisionQueueService $decisionService
    ) {}

    public function getExecutiveScorecard(string $tenantId, ?string $departmentId = null, ?string $periodKey = null): ExecutiveScorecardData
    {
        $periodKey = $periodKey ?? Carbon::now()->format('Y-m');

        $headcountKpi = $this->kpiService->calculateKpi('TOTAL_HEADCOUNT', $tenantId, $departmentId, $periodKey);
        $costKpi = $this->kpiService->calculateKpi('TOTAL_COST', $tenantId, $departmentId, $periodKey);
        $costPerFteKpi = $this->kpiService->calculateKpi('COST_PER_FTE', $tenantId, $departmentId, $periodKey);
        $prodKpi = $this->kpiService->calculateKpi('PRODUCTIVITY_SCORE', $tenantId, $departmentId, $periodKey);
        $absenceKpi = $this->kpiService->calculateKpi('ABSENCE_RATE', $tenantId, $departmentId, $periodKey);
        $turnoverKpi = $this->kpiService->calculateKpi('TURNOVER_RATE', $tenantId, $departmentId, $periodKey);

        $healthIndex = $this->healthIndexService->computeHealthIndex($tenantId, $departmentId, $periodKey);

        $criticalRisks = CommandCenterRisk::where('tenant_id', $tenantId)
            ->when($departmentId, fn($q) => $q->where('department_id', $departmentId))
            ->where('severity', 'CRITICAL')
            ->count();

        $openDecisions = CommandCenterDecisionItem::where('tenant_id', $tenantId)
            ->when($departmentId, fn($q) => $q->where('department_id', $departmentId))
            ->where('status', 'PENDING')
            ->count();

        $highlights = [
            "Composite health is {$healthIndex->healthBand} at {$healthIndex->compositeScore}/100",
            "Total headcount is currently " . (int)$headcountKpi->calculated_value . " with low voluntary turnover",
            "Productivity efficiency sits at {$prodKpi->calculated_value}% against the 85.0% baseline",
        ];

        // Persist executive snapshot
        CommandCenterSnapshot::updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'period_key' => $periodKey,
                'snapshot_code' => "SNAP-{$tenantId}-{$periodKey}",
            ],
            [
                'snapshot_name' => "Executive Workforce Intelligence Rollup - {$periodKey}",
                'period_type' => 'MONTH',
                'as_of_date' => Carbon::now()->toDateString(),
                'total_headcount' => (int)$headcountKpi->calculated_value,
                'total_fte' => (float)$headcountKpi->calculated_value * 0.95,
                'total_workforce_cost' => (float)$costKpi->calculated_value,
                'average_cost_per_fte' => (float)$costPerFteKpi->calculated_value,
                'composite_health_score' => $healthIndex->compositeScore,
                'overall_productivity_score' => (float)$prodKpi->calculated_value,
                'turnover_rate' => (float)$turnoverKpi->calculated_value,
                'absence_rate' => (float)$absenceKpi->calculated_value,
                'critical_risk_count' => $criticalRisks,
                'open_decision_count' => $openDecisions,
                'summary_metrics' => [
                    'headcount' => $headcountKpi->calculated_value,
                    'cost' => $costKpi->calculated_value,
                    'productivity' => $prodKpi->calculated_value,
                ],
                'dimension_breakdowns' => $healthIndex->contributingFactors,
                'generated_at' => Carbon::now(),
            ]
        );

        return new ExecutiveScorecardData(
            totalHeadcount: (int)$headcountKpi->calculated_value,
            totalFte: (float)$headcountKpi->calculated_value * 0.95,
            totalWorkforceCost: (float)$costKpi->calculated_value,
            averageCostPerFte: (float)$costPerFteKpi->calculated_value,
            compositeHealthScore: $healthIndex->compositeScore,
            healthBand: $healthIndex->healthBand,
            overallProductivityScore: (float)$prodKpi->calculated_value,
            turnoverRate: (float)$turnoverKpi->calculated_value,
            absenceRate: (float)$absenceKpi->calculated_value,
            criticalRiskCount: $criticalRisks,
            openDecisionCount: $openDecisions,
            periodKey: $periodKey,
            topHighlights: $highlights,
            kpiMetrics: [
                'headcount' => $headcountKpi->toArray(),
                'cost' => $costKpi->toArray(),
                'cost_per_fte' => $costPerFteKpi->toArray(),
                'productivity' => $prodKpi->toArray(),
                'absence' => $absenceKpi->toArray(),
                'turnover' => $turnoverKpi->toArray(),
            ]
        );
    }

    public function getWorkforceHealthIndex(string $tenantId, ?string $departmentId = null, ?string $periodKey = null): WorkforceHealthIndexData
    {
        return $this->healthIndexService->computeHealthIndex($tenantId, $departmentId, $periodKey);
    }

    public function getWorkforcePulse(string $tenantId, ?string $departmentId = null): WorkforcePulseData
    {
        return $this->pulseService->getPulse($tenantId, $departmentId);
    }

    public function getConsolidatedRisks(string $tenantId, ?string $departmentId = null, ?string $category = null, ?string $severity = null): array
    {
        return $this->riskService->getRisks($tenantId, $departmentId, $category, $severity);
    }

    public function getPrioritizedAlerts(string $tenantId, ?string $departmentId = null, ?string $severity = null): array
    {
        return $this->alertService->getAlerts($tenantId, $departmentId, $severity);
    }

    public function getDecisionQueue(string $tenantId, ?string $departmentId = null, ?string $status = 'PENDING'): array
    {
        return $this->decisionService->getPendingDecisions($tenantId, $departmentId);
    }

    public function getDashboardLayout(string $tenantId, string $persona = 'EXECUTIVE'): array
    {
        $dashboard = CommandCenterDashboard::firstOrCreate(
            [
                'tenant_id' => $tenantId,
                'persona' => $persona,
            ],
            [
                'dashboard_code' => "DASH-{$persona}-{$tenantId}",
                'name' => Str::title(str_replace('_', ' ', $persona)) . ' Command Cockpit',
                'is_system_default' => true,
                'layout_config' => ['columns' => 12, 'theme' => 'executive-dark'],
            ]
        );

        if ($dashboard->widgets()->count() === 0) {
            $this->seedDefaultWidgets($dashboard);
        }

        return [
            'dashboard' => $dashboard->toArray(),
            'widgets' => $dashboard->widgets()->where('is_visible', true)->get()->toArray(),
        ];
    }

    protected function seedDefaultWidgets(CommandCenterDashboard $dashboard): void
    {
        $widgets = [
            ['code' => 'WGT_HEALTH_RADAR', 'title' => 'Composite Workforce Health Index', 'type' => 'HEALTH_RADAR', 'x' => 0, 'y' => 0, 'w' => 6, 'h' => 4],
            ['code' => 'WGT_EXEC_SCORECARD', 'title' => 'Key Workforce Metrics', 'type' => 'KPI_CARD', 'x' => 6, 'y' => 0, 'w' => 6, 'h' => 4],
            ['code' => 'WGT_PULSE_MONITOR', 'title' => 'Operational Workforce Pulse', 'type' => 'CHART_TREND', 'x' => 0, 'y' => 4, 'w' => 12, 'h' => 3],
            ['code' => 'WGT_RISK_HEATMAP', 'title' => 'Consolidated Risk Center', 'type' => 'RISK_HEATMAP', 'x' => 0, 'y' => 7, 'w' => 6, 'h' => 4],
            ['code' => 'WGT_DECISION_QUEUE', 'title' => 'Action & Decision Queue', 'type' => 'DECISION_LIST', 'x' => 6, 'y' => 7, 'w' => 6, 'h' => 4],
        ];

        foreach ($widgets as $w) {
            CommandCenterWidget::create([
                'tenant_id' => $dashboard->tenant_id,
                'dashboard_id' => $dashboard->id,
                'widget_code' => $w['code'],
                'title' => $w['title'],
                'widget_type' => $w['type'],
                'grid_x' => $w['x'],
                'grid_y' => $w['y'],
                'grid_width' => $w['w'],
                'grid_height' => $w['h'],
                'is_visible' => true,
            ]);
        }
    }
}
