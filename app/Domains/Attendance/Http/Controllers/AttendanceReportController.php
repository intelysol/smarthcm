<?php

namespace App\Domains\Attendance\Http\Controllers;

use App\Domains\Attendance\Services\AttendanceAnalyticsService;
use App\Domains\Attendance\Services\AttendanceExportService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AttendanceReportController extends Controller
{
    public function __construct(
        protected AttendanceAnalyticsService $analyticsService,
        protected AttendanceExportService $exportService
    ) {}

    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();
        $metrics = $this->analyticsService->generateMetrics(
            $user->tenant_id,
            $request->query('from'),
            $request->query('to')
        );

        return response()->json(['data' => $metrics]);
    }

    public function payrollExport(Request $request): JsonResponse
    {
        $user = $request->user();
        $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $payload = $this->exportService->generatePayrollExportPayload(
            $user->tenant_id,
            $request->input('start_date'),
            $request->input('end_date')
        );

        return response()->json([
            'message' => 'Payroll integration payload generated.',
            'count' => count($payload),
            'data' => $payload,
        ]);
    }

    public function exportCsv(Request $request): Response
    {
        $user = $request->user();
        $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $payload = $this->exportService->generatePayrollExportPayload(
            $user->tenant_id,
            $request->input('start_date'),
            $request->input('end_date')
        );

        $csv = $this->exportService->generateCsv($payload);

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="attendance_payroll_export_' . now()->format('Ymd_His') . '.csv"',
        ]);
    }
}
