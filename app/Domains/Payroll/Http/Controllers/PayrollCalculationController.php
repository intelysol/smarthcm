<?php

namespace App\Domains\Payroll\Http\Controllers;

use App\Domains\Employee\Models\Employee;
use App\Domains\Payroll\Models\PayrollRun;
use App\Domains\Payroll\Services\PayrollCalculationEngine;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayrollCalculationController extends Controller
{
    public function __construct(protected PayrollCalculationEngine $calculationEngine) {}

    public function calculateEmployee(PayrollRun $run, Employee $employee): JsonResponse
    {
        $snapshot = $this->calculationEngine->calculateEmployeePayroll($run, $employee);

        return response()->json([
            'message' => 'Employee calculation completed.',
            'data' => $snapshot->load('lines'),
        ]);
    }
}
