<?php

namespace App\Domains\Analytics\Http\Controllers;

use App\Domains\Analytics\Services\HcmDataQualityService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HcmDataQualityController extends Controller
{
    public function __construct(
        protected HcmDataQualityService $qualityService
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? 'default';
        $summary = $this->qualityService->runDataQualityValidation($tenantId);

        if ($request->wantsJson()) {
            return response()->json($summary);
        }

        return view('analytics.quality.index', compact('summary'));
    }

    public function evaluate(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? 'default';
        $summary = $this->qualityService->runDataQualityValidation($tenantId);

        return response()->json([
            'message' => 'Data quality validation evaluated successfully.',
            'data' => $summary,
        ]);
    }
}
