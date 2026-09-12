<?php

namespace App\Domains\Payroll\Http\Controllers;

use App\Domains\Payroll\Models\CompensationComponent;
use App\Domains\Payroll\Requests\CompensationComponentRequest;
use App\Domains\Payroll\Services\CompensationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompensationController extends Controller
{
    public function __construct(protected CompensationService $compensationService) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $components = CompensationComponent::query()
            ->where('tenant_id', $tenantId)
            ->orderBy('priority_order')
            ->get();

        return response()->json($components);
    }

    public function store(CompensationComponentRequest $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $component = $this->compensationService->createComponent($tenantId, $request->validated(), $request->user());

        return response()->json([
            'message' => 'Compensation component created successfully.',
            'data' => $component,
        ], 201);
    }
}
