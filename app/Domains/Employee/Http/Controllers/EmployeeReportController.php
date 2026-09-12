<?php

namespace App\Domains\Employee\Http\Controllers;

use App\Domains\Employee\Services\EmployeeReportService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeReportController extends Controller
{
    public function masterList(Request $request, EmployeeReportService $reports): JsonResponse
    {
        $user = $request->user();
        abort_if($user === null || ! $user->hasPermission('employee.export'), 403);

        return response()->json(['data' => $reports->masterList((string) $user->tenant_id)]);
    }
}
