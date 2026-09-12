<?php

declare(strict_types=1);

namespace App\Domains\Performance\Http\Controllers;

use App\Domains\Performance\Services\PerformanceReportingService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PerformanceReportingController extends Controller
{
    public function cycleMetrics(Request $request, string $cycleId, PerformanceReportingService $service): JsonResponse
    {
        $metrics = $service->getCycleMetrics($cycleId, $request->user()->tenant_id);
        return response()->json(['data' => $metrics]);
    }
}