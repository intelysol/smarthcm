<?php

namespace App\Domains\Payroll\Http\Controllers;

use App\Domains\Employee\Models\Employee;
use App\Domains\Payroll\Models\PayrollPeriod;
use App\Domains\Payroll\Services\PayrollInputService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayrollInputController extends Controller
{
    public function __construct(protected PayrollInputService $inputService) {}

    public function collect(PayrollPeriod $period, Employee $employee, Request $request): JsonResponse
    {
        $input = $this->inputService->collectInputsForEmployee($period, $employee, $request->user());

        return response()->json([
            'message' => 'Inputs collected successfully.',
            'data' => $input,
        ]);
    }
}
