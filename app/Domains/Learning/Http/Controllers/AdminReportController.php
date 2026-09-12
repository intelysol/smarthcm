<?php

namespace App\Domains\Learning\Http\Controllers;

use App\Domains\Learning\Services\LearningReportService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminReportController extends Controller
{
    public function completion(Request $request, LearningReportService $service): JsonResponse
    {
        abort_unless($request->user()?->hasPermission('hcm.learning.report.view'), 403);

        $data = $service->completionReport((string) $request->user()->tenant_id, $request->query());

        return response()->json(['data' => $data]);
    }

    public function compliance(Request $request, LearningReportService $service): JsonResponse
    {
        abort_unless($request->user()?->hasPermission('hcm.learning.report.view'), 403);

        $data = $service->complianceReport((string) $request->user()->tenant_id, $request->query());

        return response()->json(['data' => $data]);
    }

    public function certifications(Request $request, LearningReportService $service): JsonResponse
    {
        abort_unless($request->user()?->hasPermission('hcm.learning.report.view'), 403);

        $data = $service->certificationReport((string) $request->user()->tenant_id, $request->query());

        return response()->json(['data' => $data]);
    }

    public function cost(Request $request, LearningReportService $service): JsonResponse
    {
        abort_unless($request->user()?->hasPermission('hcm.learning.cost.view') || $request->user()?->hasPermission('hcm.learning.report.view'), 403);

        $data = $service->costReport((string) $request->user()->tenant_id, $request->query());

        return response()->json(['data' => $data]);
    }
}
