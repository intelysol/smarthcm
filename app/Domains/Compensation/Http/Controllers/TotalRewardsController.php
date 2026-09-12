<?php

declare(strict_types=1);

namespace App\Domains\Compensation\Http\Controllers;

use App\Domains\Compensation\Models\TotalRewardsStatement;
use App\Domains\Compensation\Services\TotalRewardsService;
use App\Domains\Employee\Models\Employee;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TotalRewardsController extends Controller
{
    public function __construct(
        protected TotalRewardsService $totalRewardsService
    ) {}

    public function show(Request $request, Employee $employee, int $year): JsonResponse
    {
        $statement = TotalRewardsStatement::where('employee_id', $employee->id)
            ->where('year', $year)
            ->first();

        if (! $statement) {
            return response()->json(['message' => 'Total rewards statement not found for specified year.'], 404);
        }

        return response()->json(['data' => $statement]);
    }

    public function generate(Request $request, Employee $employee): JsonResponse
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
            'base_salary' => 'required|numeric|min:0',
            'bonus_amount' => 'nullable|numeric|min:0',
            'benefits_value' => 'nullable|numeric|min:0',
            'equity_value' => 'nullable|numeric|min:0',
            'retirement_contribution' => 'nullable|numeric|min:0',
            'other_allowances' => 'nullable|numeric|min:0',
        ]);

        $statement = $this->totalRewardsService->generateStatement(
            $request->user(),
            $employee,
            (int) $validated['year'],
            (float) $validated['base_salary'],
            (float) ($validated['bonus_amount'] ?? 0.0),
            (float) ($validated['benefits_value'] ?? 0.0),
            (float) ($validated['equity_value'] ?? 0.0),
            (float) ($validated['retirement_contribution'] ?? 0.0),
            (float) ($validated['other_allowances'] ?? 0.0)
        );

        return response()->json(['data' => $statement], 201);
    }
}
