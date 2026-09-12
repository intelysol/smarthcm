<?php

namespace App\Domains\SelfService\Http\Controllers;

use App\Domains\SelfService\Services\PortalService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PortalDashboardController extends Controller
{
    public function employee(Request $request, PortalService $portal): JsonResponse
    {
        $user = $request->user();
        abort_if($user === null || ! $user->hasPermission('ess.view'), 403);

        return response()->json(['data' => $portal->dashboard($portal->employeeFor($user))]);
    }

    public function manager(Request $request, PortalService $portal): JsonResponse
    {
        $user = $request->user();
        abort_if($user === null || ! $user->hasPermission('manager.dashboard'), 403);

        return response()->json(['data' => $portal->managerDashboard($portal->employeeFor($user))]);
    }
}
