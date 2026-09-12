<?php

namespace App\Domains\Analytics\Http\Controllers;

use App\Domains\Analytics\Requests\ExecuteHcmReportRequest;
use App\Domains\Analytics\Services\HcmReportBuilderService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class HcmReportBuilderController extends Controller
{
    public function __construct(
        protected HcmReportBuilderService $reportService
    ) {}

    public function index(): View
    {
        return view('analytics.reports.index');
    }

    public function builder(): View
    {
        return view('analytics.reports.builder');
    }

    public function execute(ExecuteHcmReportRequest $request): JsonResponse|Response
    {
        $user = $request->user();
        $tenantId = $user?->tenant_id ?? 'default';
        $dataset = $request->input('dataset');
        $format = $request->input('format', 'json');

        $result = $this->reportService->executeReportQuery($tenantId, $dataset, $request->validated(), $user);

        if ($format === 'csv') {
            $csv = $this->reportService->generateCsvExport($result);
            return response($csv, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="hcm_report_' . $dataset . '_' . now()->format('Ymd_His') . '.csv"',
            ]);
        }

        return response()->json($result);
    }
}
