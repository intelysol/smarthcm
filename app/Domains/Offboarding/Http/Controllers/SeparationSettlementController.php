<?php

namespace App\Domains\Offboarding\Http\Controllers;

use App\Domains\Offboarding\Models\SeparationRequest;
use App\Domains\Offboarding\Services\SeparationFinalSettlementService;
use App\Domains\Offboarding\Services\SeparationSecurityService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SeparationSettlementController extends Controller
{
    public function __construct(
        protected SeparationFinalSettlementService $settlementService,
        protected SeparationSecurityService $securityService
    ) {
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $separation = SeparationRequest::with('finalSettlement')->findOrFail($id);
        $this->securityService->authorizeRequestAccess($request->user(), $separation);

        return response()->json($separation->finalSettlement);
    }

    public function recordSnapshot(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'gross_payable' => 'required|numeric|min:0',
            'deductions' => 'required|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'payment_date' => 'nullable|date',
            'snapshot_data' => 'nullable|array',
        ]);

        $separation = SeparationRequest::findOrFail($id);
        $this->securityService->authorizeRequestAccess($request->user(), $separation);

        $settlement = $this->settlementService->recordSettlementSnapshot($separation, $validated, $request->user());
        return response()->json($settlement, 201);
    }
}
