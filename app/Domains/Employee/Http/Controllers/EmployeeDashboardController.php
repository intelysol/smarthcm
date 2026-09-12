<?php

namespace App\Domains\Employee\Http\Controllers;

use App\Domains\Employee\Services\EmployeeReportService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeDashboardController extends Controller
{
    public function __invoke(Request $request, EmployeeReportService $reports): JsonResponse
    {
        $user = $request->user();
        abort_if($user === null || ! $user->hasPermission('employee.view'), 403);

        return response()->json(['widgets' => $reports->dashboard((string) $user->tenant_id)]);
    }
}
