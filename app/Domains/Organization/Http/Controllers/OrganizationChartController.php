<?php

namespace App\Domains\Organization\Http\Controllers;

use App\Domains\Organization\Services\OrganizationChartService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrganizationChartController extends Controller
{
    public function __invoke(Request $request, OrganizationChartService $charts): JsonResponse
    {
        $user = $request->user();

        abort_if($user === null || ! $user->hasPermission('organization-chart.view'), 403);

        return response()->json($charts->forTenant((string) $user->tenant_id));
    }
}
