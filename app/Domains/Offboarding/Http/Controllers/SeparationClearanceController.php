<?php

namespace App\Domains\Offboarding\Http\Controllers;

use App\Domains\Offboarding\Models\SeparationClearance;
use App\Domains\Offboarding\Models\SeparationClearanceItem;
use App\Domains\Offboarding\Models\SeparationRequest;
use App\Domains\Offboarding\Services\SeparationClearanceService;
use App\Domains\Offboarding\Services\SeparationSecurityService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SeparationClearanceController extends Controller
{
    public function __construct(
        protected SeparationClearanceService $clearanceService,
        protected SeparationSecurityService $securityService
    ) {
    }

    public function index(Request $request, string $id): JsonResponse
    {
        $separation = SeparationRequest::with('clearances.items')->findOrFail($id);
        $this->securityService->authorizeRequestAccess($request->user(), $separation);

        return response()->json($separation->clearances);
    }

    public function clearItem(Request $request, string $itemId): JsonResponse
    {
        $item = SeparationClearanceItem::with('clearance.request')->findOrFail($itemId);
        $this->securityService->authorizeRequestAccess($request->user(), $item->clearance->request);

        $cleared = $this->clearanceService->clearItem($item, $request->user(), $request->input('comments'));
        return response()->json($cleared);
    }

    public function waiveDepartment(Request $request, string $clearanceId): JsonResponse
    {
        $request->validate(['reason' => 'required|string|max:255']);
        $clearance = SeparationClearance::with('request')->findOrFail($clearanceId);
        $this->securityService->authorizeRequestAccess($request->user(), $clearance->request);

        $waived = $this->clearanceService->waiveClearance($clearance, $request->user(), $request->input('reason'));
        return response()->json($waived);
    }
}
