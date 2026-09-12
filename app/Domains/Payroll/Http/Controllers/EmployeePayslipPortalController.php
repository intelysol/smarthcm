<?php

namespace App\Domains\Payroll\Http\Controllers;

use App\Domains\Employee\Models\Employee;
use App\Domains\Payroll\Models\PayrollPayslip;
use App\Domains\Payroll\Services\PayrollAuthorizationService;
use App\Domains\Payroll\Services\PayslipService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeePayslipPortalController extends Controller
{
    public function __construct(
        protected PayslipService $payslipService,
        protected PayrollAuthorizationService $authService
    ) {}

    public function myPayslips(Request $request): JsonResponse
    {
        $user = $request->user();
        $employee = Employee::query()->where('user_id', $user->id)->first();

        if (! $employee) {
            return response()->json(['message' => 'No employee profile linked to current user.'], 404);
        }

        $payslips = $this->payslipService->getEmployeePayslips($employee);
        return response()->json($payslips);
    }
}
