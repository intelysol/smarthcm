<?php

namespace App\Domains\WorkforceProductivity\Http\Controllers;

use App\Domains\WorkforceProductivity\Models\HcmProductivityMeasurement;
use App\Domains\WorkforceProductivity\Services\ProductivityExportService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductivityExplorerController extends Controller
{
    public function __construct(
        protected ProductivityExportService $exportService
    ) {}

    public function explorer(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID') ?? $request->query('tenant_id');
        if (! $tenantId) {
            return response()->json(['error' => 'Tenant ID is required'], 400);
        }

        $query = HcmProductivityMeasurement::with(['metricDefinition', 'department', 'lines'])
            ->where('tenant_id', $tenantId);

        if ($request->has('department_id')) {
            $query->where('department_id', $request->query('department_id'));
        }
        if ($request->has('metric_id')) {
            $query->where('metric_definition_id', $request->query('metric_id'));
        }
        if ($request->has('period_type')) {
            $query->where('period_type', $request->query('period_type'));
        }
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('period_start', [$request->query('start_date'), $request->query('end_date')]);
        }

        $sortField = $request->query('sort_by', 'period_end');
        $sortDirection = $request->query('sort_dir', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $results = $query->paginate($request->query('per_page', 25));

        return response()->json($results);
    }

    public function exportCsv(Request $request): StreamedResponse|JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID') ?? $request->query('tenant_id');
        $startDate = $request->query('start_date') ?? now()->subYear()->toDateString();
        $endDate = $request->query('end_date') ?? now()->toDateString();

        if (! $tenantId) {
            return response()->json(['error' => 'Tenant ID is required'], 400);
        }

        $csv = $this->exportService->exportMeasurementsCsv($tenantId, $startDate, $endDate);

        return response()->streamDownload(function () use ($csv) {
            echo $csv;
        }, 'productivity_measurements_' . now()->format('Ymd_His') . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}
