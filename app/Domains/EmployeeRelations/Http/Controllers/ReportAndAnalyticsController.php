<?php

namespace App\Domains\EmployeeRelations\Http\Controllers;

use App\Domains\EmployeeRelations\Services\EmployeeRelationAnalyticsService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportAndAnalyticsController extends Controller
{
    public function __construct(
        protected EmployeeRelationAnalyticsService $analyticsService
    ) {}

    public function reports(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('hcm.employee_relations.report.view'), 403);

        $metrics = $this->analyticsService->generateMetrics(
            $request->user()->tenant_id,
            $request->input('from'),
            $request->input('to')
        );

        return response()->json($metrics);
    }

    public function analytics(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('hcm.employee_relations.report.view'), 403);

        $metrics = $this->analyticsService->generateMetrics(
            $request->user()->tenant_id,
            $request->input('from'),
            $request->input('to')
        );

        return response()->json([
            'dashboard_kpis' => [
                'case_volume' => $metrics['total_cases'],
                'open_cases' => $metrics['open_cases'],
                'average_resolution_days' => $metrics['average_resolution_days'],
                'sla_compliance_rate' => $metrics['sla_compliance_rate'],
                'investigation_completion_rate' => $metrics['investigation_completion_rate'],
                'appeal_rate' => $metrics['appeal_rate'],
                'corrective_action_completion_rate' => $metrics['corrective_action_completion_rate'],
            ],
            'breakdown_by_type' => $metrics['by_type'],
            'breakdown_by_severity' => $metrics['by_severity'],
        ]);
    }
}
