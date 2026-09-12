<?php

namespace App\Domains\Organization\Http\Controllers;

use App\Domains\Organization\Services\OrganizationReportService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrganizationDashboardController extends Controller
{
    public function __invoke(Request $request, OrganizationReportService $reports): JsonResponse
    {
        $user = $request->user();

        abort_if($user === null || ! $user->hasPermission('organization-dashboard.view'), 403);

        return response()->json([
            'widgets' => $reports->summary((string) $user->tenant_id),
        ]);
    }
}
