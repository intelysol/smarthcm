<?php

namespace App\Domains\Lifecycle\Http\Controllers;

use App\Domains\Lifecycle\Models\PersonnelActionRequest;
use App\Domains\Lifecycle\Services\PersonnelActionAiService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PersonnelActionAiController extends Controller
{
    public function __construct(protected PersonnelActionAiService $aiService)
    {
    }

    public function summarize(string $id): JsonResponse
    {
        $action = PersonnelActionRequest::with(['employee', 'actionType', 'changes'])->findOrFail($id);
        $summary = $this->aiService->summarizeAction($action);

        return response()->json($summary);
    }

    public function draftPromotionLetter(string $id): JsonResponse
    {
        $action = PersonnelActionRequest::with(['employee', 'actionType'])->findOrFail($id);
        $draft = $this->aiService->draftPromotionLetter($action);

        return response()->json($draft);
    }

    public function query(Request $request): JsonResponse
    {
        $request->validate(['query' => 'required|string|max:1000']);
        $answer = $this->aiService->processAiInquiry($request->user()->tenant_id, $request->input('query'));

        return response()->json($answer);
    }
}
