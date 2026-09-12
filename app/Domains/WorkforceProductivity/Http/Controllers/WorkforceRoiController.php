<?php

namespace App\Domains\WorkforceProductivity\Http\Controllers;

use App\Domains\WorkforceProductivity\Models\HcmProductivityRoiCalculation;
use App\Domains\WorkforceProductivity\Services\WorkforceROIService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkforceRoiController extends Controller
{
    public function __construct(
        protected WorkforceROIService $roiService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID') ?? $request->query('tenant_id');
        if (! $tenantId) {
            return response()->json(['error' => 'Tenant ID is required'], 400);
        }

        $calculations = HcmProductivityRoiCalculation::with(['model', 'department'])
            ->where('tenant_id', $tenantId)
            ->latest()
            ->paginate(20);

        return response()->json($calculations);
    }

    public function storeTrainingRoi(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id' => 'required|uuid',
            'training_program_name' => 'required|string',
            'direct_training_cost' => 'required|numeric',
            'training_hours' => 'required|numeric',
            'hourly_wage_rate' => 'required|numeric',
            'pre_training_hourly_output' => 'required|numeric',
            'post_training_hourly_output' => 'required|numeric',
            'unit_value' => 'required|numeric',
            'evaluation_period_hours' => 'nullable|integer',
            'department_id' => 'nullable|uuid',
        ]);

        $calculation = $this->roiService->calculateTrainingRoi(
            tenantId: $validated['tenant_id'],
            trainingProgramName: $validated['training_program_name'],
            directTrainingCost: (float) $validated['direct_training_cost'],
            trainingHours: (float) $validated['training_hours'],
            hourlyWageRate: (float) $validated['hourly_wage_rate'],
            preTrainingHourlyOutput: (float) $validated['pre_training_hourly_output'],
            postTrainingHourlyOutput: (float) $validated['post_training_hourly_output'],
            unitValue: (float) $validated['unit_value'],
            evaluationPeriodHours: (int) ($validated['evaluation_period_hours'] ?? 500),
            departmentId: $validated['department_id'] ?? null
        );

        return response()->json(['data' => $calculation], 201);
    }

    public function storeRecruitmentRoi(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id' => 'required|uuid',
            'role_name' => 'required|string',
            'recruitment_cost' => 'required|numeric',
            'ramp_up_months' => 'required|numeric',
            'standard_monthly_output_value' => 'required|numeric',
            'ramp_up_efficiency_pct' => 'nullable|numeric',
            'department_id' => 'nullable|uuid',
        ]);

        $calculation = $this->roiService->calculateRecruitmentRoi(
            tenantId: $validated['tenant_id'],
            roleName: $validated['role_name'],
            recruitmentCost: (float) $validated['recruitment_cost'],
            rampUpMonths: (float) $validated['ramp_up_months'],
            standardMonthlyOutputValue: (float) $validated['standard_monthly_output_value'],
            rampUpEfficiencyPct: (float) ($validated['ramp_up_efficiency_pct'] ?? 60.0),
            departmentId: $validated['department_id'] ?? null
        );

        return response()->json(['data' => $calculation], 201);
    }
}
