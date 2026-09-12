<?php

namespace App\Domains\Analytics\Http\Controllers;

use App\Domains\Analytics\Requests\NaturalLanguageAiQueryRequest;
use App\Domains\Analytics\Services\HcmAiAnalyticsService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HcmAiAnalyticsController extends Controller
{
    public function __construct(
        protected HcmAiAnalyticsService $aiService
    ) {}

    public function index(): View
    {
        return view('analytics.ai.assistant');
    }

    public function query(NaturalLanguageAiQueryRequest $request): JsonResponse
    {
        $user = $request->user();
        $tenantId = $user?->tenant_id ?? 'default';
        $question = $request->input('query');

        $response = $this->aiService->processNaturalLanguageQuery($tenantId, $question, $user);

        return response()->json($response);
    }
}
