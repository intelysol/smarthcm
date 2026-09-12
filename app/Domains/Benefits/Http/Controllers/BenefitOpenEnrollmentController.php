<?php

namespace App\Domains\Benefits\Http\Controllers;

use App\Domains\Benefits\Models\BenefitEnrollmentWindow;
use App\Domains\Benefits\Services\BenefitOpenEnrollmentService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BenefitOpenEnrollmentController extends Controller
{
    public function __construct(
        protected BenefitOpenEnrollmentService $openEnrollmentService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? (string) $request->header('X-Tenant-ID');
        $windows = BenefitEnrollmentWindow::where('tenant_id', $tenantId)->orderByDesc('plan_year')->get();

        return response()->json([
            'success' => true,
            'data' => $windows,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'plan_year' => 'required|integer|min:2020|max:2100',
            'start_date' => 'required|date',
            'close_date' => 'required|date|after_or_equal:start_date',
            'effective_date' => 'required|date',
            'allow_late_enrollment' => 'nullable|boolean',
            'description' => 'nullable|string',
        ]);

        $tenantId = $request->user()?->tenant_id ?? (string) $request->header('X-Tenant-ID');
        $window = $this->openEnrollmentService->createWindow($tenantId, $validated, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Open enrollment window created.',
            'data' => $window,
        ], 201);
    }

    public function open(BenefitEnrollmentWindow $window, Request $request): JsonResponse
    {
        $opened = $this->openEnrollmentService->openWindow($window, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Open enrollment window is now active.',
            'data' => $opened,
        ]);
    }

    public function progress(BenefitEnrollmentWindow $window): JsonResponse
    {
        $progress = $this->openEnrollmentService->getProgress($window);

        return response()->json([
            'success' => true,
            'data' => $progress,
        ]);
    }

    public function finalize(BenefitEnrollmentWindow $window, Request $request): JsonResponse
    {
        $finalized = $this->openEnrollmentService->finalizeWindow($window, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Open enrollment finalized and locked.',
            'data' => $finalized,
        ]);
    }
}
