<?php

namespace App\Domains\Payroll\Http\Controllers;

use App\Domains\Payroll\Models\CompensationStructure;
use App\Domains\Payroll\Requests\CompensationStructureRequest;
use App\Domains\Payroll\Services\CompensationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SalaryStructureController extends Controller
{
    public function __construct(protected CompensationService $compensationService) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $structures = CompensationStructure::query()
            ->where('tenant_id', $tenantId)
            ->with(['structureComponents.component'])
            ->get();

        return response()->json($structures);
    }

    public function store(CompensationStructureRequest $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $structure = $this->compensationService->createStructure(
            $tenantId,
            $request->validated(),
            $request->input('components', []),
            $request->user()
        );

        return response()->json([
            'message' => 'Salary structure created successfully.',
            'data' => $structure->load('structureComponents.component'),
        ], 201);
    }
}
