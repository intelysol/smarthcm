<?php

namespace App\Domains\Benefits\Http\Controllers;

use App\Domains\Benefits\Services\BenefitStatementService;
use App\Domains\Employee\Models\Employee;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BenefitStatementController extends Controller
{
    public function __construct(
        protected BenefitStatementService $statementService
    ) {}

    public function show(Employee $employee, Request $request): JsonResponse
    {
        $year = (int) ($request->query('year') ?? now()->year);
        $statement = $this->statementService->getStatement($employee, $year);

        if (! $statement) {
            $statement = $this->statementService->generateStatement($employee, $year, $request->user());
        }

        return response()->json([
            'success' => true,
            'employee_id' => $employee->id,
            'year' => $year,
            'data' => $statement,
        ]);
    }

    public function generate(Employee $employee, Request $request): JsonResponse
    {
        $year = (int) ($request->input('year') ?? now()->year);
        $statement = $this->statementService->generateStatement($employee, $year, $request->user());

        return response()->json([
            'success' => true,
            'message' => "Annual benefit statement for year {$year} generated.",
            'data' => $statement,
        ]);
    }
}
