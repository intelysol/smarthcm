<?php

declare(strict_types=1);

namespace App\Domains\Platform\Http\Controllers;

use App\Domains\Platform\Services\HealthCheckService;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class HealthCheckController extends Controller
{
    public function __construct(private readonly HealthCheckService $healthService) {}

    public function health(): JsonResponse
    {
        $report = $this->healthService->getDetailedHealth();
        $statusCode = ($report['status'] === 'ok') ? 200 : 503;

        return response()->json($report, $statusCode);
    }

    public function live(): JsonResponse
    {
        $report = $this->healthService->getLiveness();

        return response()->json($report, 200);
    }

    public function ready(): JsonResponse
    {
        $report = $this->healthService->getReadiness();
        $statusCode = $report['ready'] ? 200 : 503;

        return response()->json($report, $statusCode);
    }

    public function dependencies(): JsonResponse
    {
        $report = $this->healthService->getDependenciesHealth();
        $statusCode = $report['healthy'] ? 200 : 503;

        return response()->json($report, $statusCode);
    }

    public function services(): JsonResponse
    {
        $report = $this->healthService->getServicesHealth();

        return response()->json($report, 200);
    }
}
