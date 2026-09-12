<?php

namespace App\Domains\Recruitment\Http\Controllers;

use App\Domains\Recruitment\Services\RecruitmentAnalyticsService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecruitmentAnalyticsController extends Controller
{
    public function __construct(protected RecruitmentAnalyticsService $analyticsService)
    {
    }

    public function funnel(Request $request): JsonResponse
    {
        $funnel = $this->analyticsService->getRecruitmentFunnel(
            $request->user()->tenant_id,
            $request->input('requisition_id')
        );

        return response()->json($funnel);
    }

    public function kpis(Request $request): JsonResponse
    {
        $kpis = $this->analyticsService->getRecruitmentKpis($request->user()->tenant_id);
        return response()->json($kpis);
    }
}
