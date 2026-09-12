<?php

namespace App\Domains\Analytics\Http\Controllers;

use App\Domains\Analytics\Services\HcmDashboardService;
use App\Domains\Employee\Models\Employee;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HcmDashboardController extends Controller
{
    public function __construct(
        protected HcmDashboardService $dashboardService
    ) {}

    public function chro(Request $request): View|JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? 'default';
        $data = $this->dashboardService->getChroDashboard($tenantId);

        if ($request->wantsJson()) {
            return response()->json($data);
        }

        return view('analytics.chro', compact('data'));
    }

    public function manager(Request $request): View|JsonResponse
    {
        $user = $request->user();
        $tenantId = $user?->tenant_id ?? 'default';
        
        $employee = Employee::where('tenant_id', $tenantId)->where('user_id', $user?->id)->first();
        if (! $employee) {
            $employee = Employee::where('tenant_id', $tenantId)->first();
        }

        if (! $employee) {
            abort(404, 'Employee profile not found for current manager.');
        }

        $data = $this->dashboardService->getManagerDashboard($tenantId, $employee);

        if ($request->wantsJson()) {
            return response()->json($data);
        }

        return view('analytics.manager', compact('data'));
    }
}
