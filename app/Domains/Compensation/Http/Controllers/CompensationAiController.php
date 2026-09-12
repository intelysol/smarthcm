<?php

declare(strict_types=1);

namespace App\Domains\Compensation\Http\Controllers;

use App\Domains\Compensation\Services\CompensationAiAdvisoryService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompensationAiController extends Controller
{
    public function __construct(
        protected CompensationAiAdvisoryService $aiService
    ) {}

    public function draftJustification(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_name' => 'required|string|max:100',
            'performance_rating' => 'required|string|max:50',
            'compa_ratio' => 'required|numeric|min:0',
            'proposed_percentage' => 'required|numeric|min:0',
            'context' => 'nullable|string',
        ]);

        $result = $this->aiService->draftMeritJustification(
            $validated['employee_name'],
            $validated['performance_rating'],
            (float) $validated['compa_ratio'],
            (float) $validated['proposed_percentage'],
            $validated['context'] ?? null
        );

        return response()->json(['data' => $result]);
    }
}
