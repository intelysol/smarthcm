<?php

declare(strict_types=1);

namespace App\Domains\Compensation\Http\Controllers;

use App\Domains\Compensation\Models\CompensationCycle;
use App\Domains\Compensation\Services\CompensationPayrollIntegrationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompensationPayrollIntegrationController extends Controller
{
    public function __construct(
        protected CompensationPayrollIntegrationService $integrationService
    ) {}

    public function export(Request $request, CompensationCycle $cycle): JsonResponse
    {
        $export = $this->integrationService->exportApprovedCycleToPayroll($request->user(), $cycle);

        return response()->json([
            'message' => 'Approved compensation plan exported to Payroll successfully.',
            'data' => $export,
        ], 201);
    }
}
