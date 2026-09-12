<?php

namespace App\Domains\Payroll\Http\Controllers;

use App\Domains\Employee\Models\Employee;
use App\Domains\Payroll\Models\EmployeeCompensation;
use App\Domains\Payroll\Requests\EmployeeCompensationRequest;
use App\Domains\Payroll\Services\CompensationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeCompensationController extends Controller
{
    public function __construct(protected CompensationService $compensationService) {}

    public function show(Employee $employee): JsonResponse
    {
        $compensations = EmployeeCompensation::query()
            ->where('tenant_id', $employee->tenant_id)
            ->where('employee_id', $employee->id)
            ->with(['components.component', 'structure'])
            ->orderByDesc('effective_from')
            ->get();

        return response()->json($compensations);
    }

    public function store(Employee $employee, EmployeeCompensationRequest $request): JsonResponse
    {
        $comp = $this->compensationService->assignCompensation(
            $employee,
            $request->validated(),
            $request->input('components', []),
            $request->user()
        );

        return response()->json([
            'message' => 'Employee compensation assigned successfully.',
            'data' => $comp,
        ], 201);
    }
}
