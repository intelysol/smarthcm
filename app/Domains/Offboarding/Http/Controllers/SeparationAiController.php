<?php

namespace App\Domains\Offboarding\Http\Controllers;

use App\Domains\Offboarding\Models\SeparationRequest;
use App\Domains\Offboarding\Services\SeparationAiService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SeparationAiController extends Controller
{
    public function __construct(protected SeparationAiService $aiService)
    {
    }

    public function summarize(string $id): JsonResponse
    {
        $separation = SeparationRequest::with(['employee', 'separationType', 'clearances'])->findOrFail($id);
        $summary = $this->aiService->summarizeOffboardingStatus($separation);

        return response()->json($summary);
    }

    public function draftRelievingLetter(string $id): JsonResponse
    {
        $separation = SeparationRequest::with(['employee', 'separationType'])->findOrFail($id);
        $letter = $this->aiService->draftRelievingLetter($separation);

        return response()->json($letter);
    }

    public function query(Request $request): JsonResponse
    {
        $request->validate(['query' => 'required|string|max:1000']);
        $answer = $this->aiService->processAiInquiry($request->user()->tenant_id, $request->input('query'));

        return response()->json($answer);
    }
}
