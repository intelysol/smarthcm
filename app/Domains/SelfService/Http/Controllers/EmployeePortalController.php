<?php

namespace App\Domains\SelfService\Http\Controllers;

use App\Domains\Employee\Models\Employee;
use App\Domains\SelfService\Services\EmployeePortalService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeePortalController extends Controller
{
    public function __construct(
        protected EmployeePortalService $portalService
    ) {}

    public function dashboard(Request $request): View|JsonResponse
    {
        $user = $request->user();
        $employee = Employee::where('tenant_id', $user->tenant_id)->where(function ($q) use ($user) {
            $q->where('user_id', $user->id)->orWhere('id', $user->employee_id);
        })->first() ?? Employee::where('tenant_id', $user->tenant_id)->first();

        if (! $employee) {
            return response()->json(['message' => 'Employee record not found'], 404);
        }

        $data = $this->portalService->getEmployeeDashboard($employee);

        if ($request->wantsJson()) {
            return response()->json($data);
        }

        return view('self-service.dashboard', $data);
    }
}
