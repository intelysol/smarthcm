<?php

namespace App\Domains\Organization\Http\Controllers;

use App\Domains\Organization\Services\OrganizationReportService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrganizationReportController extends Controller
{
    public function summary(Request $request, OrganizationReportService $reports): JsonResponse
    {
        $user = $request->user();

        abort_if($user === null || ! $user->hasPermission('organization-report.view'), 403);

        return response()->json($reports->summary((string) $user->tenant_id));
    }

    public function departments(Request $request, OrganizationReportService $reports): JsonResponse
    {
        $user = $request->user();

        abort_if($user === null || ! $user->hasPermission('organization-report.view'), 403);

        return response()->json($reports->departmentStructure((string) $user->tenant_id));
    }
}
