<?php

namespace App\Domains\WorkforceProductivity\Http\Controllers;

use App\Domains\WorkforceProductivity\Services\ProductivityUtilizationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductivityUtilizationController extends Controller
{
    public function __construct(
        protected ProductivityUtilizationService $utilizationService
    ) {}

    public function utilization(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID') ?? $request->query('tenant_id');
        $departmentId = $request->query('department_id');
        $startDate = $request->query('start_date') ?? now()->startOfMonth()->toDateString();
        $endDate = $request->query('end_date') ?? now()->endOfMonth()->toDateString();
        $availableCapacity = (float) ($request->query('available_capacity_hours') ?? 1600.0);
        $scheduledHours = (float) ($request->query('scheduled_hours') ?? 1600.0);

        if (! $tenantId) {
            return response()->json(['error' => 'Tenant ID is required'], 400);
        }

        $result = $this->utilizationService->calculateUtilization(
            $tenantId,
            $departmentId,
            $startDate,
            $endDate,
            $availableCapacity,
            $scheduledHours
        );

        return response()->json(['data' => $result->toArray()]);
    }
}
