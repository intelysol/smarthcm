<?php

namespace App\Domains\EmployeeDocuments\Http\Controllers;

use App\Domains\EmployeeDocuments\Services\EmployeeDocumentAnalyticsService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeDocumentAnalyticsController extends Controller
{
    public function __construct(protected EmployeeDocumentAnalyticsService $analyticsService)
    {
    }

    public function metrics(Request $request): JsonResponse
    {
        $metrics = $this->analyticsService->getMetrics($request->user()->tenant_id);
        return response()->json($metrics);
    }
}
