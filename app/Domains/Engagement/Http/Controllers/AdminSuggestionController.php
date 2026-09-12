<?php

namespace App\Domains\Engagement\Http\Controllers;

use App\Domains\Employee\Models\Employee;
use App\Domains\Engagement\Models\EmployeeSuggestion;
use App\Domains\Engagement\Resources\EmployeeSuggestionResource;
use App\Domains\Engagement\Services\EmployeeSuggestionService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminSuggestionController extends Controller
{
    public function __construct(
        protected EmployeeSuggestionService $suggestionService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $suggestions = EmployeeSuggestion::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->with(['employee'])
            ->latest()
            ->paginate(30);

        return response()->json($suggestions);
    }

    public function updateStatus(Request $request, EmployeeSuggestion $suggestion): JsonResponse
    {
        $request->validate([
            'status' => ['required', 'string', 'in:submitted,under_review,accepted,implemented,rejected,deferred,archived'],
            'review_notes' => ['nullable', 'string'],
        ]);

        $reviewer = Employee::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->where('user_id', $request->user()->id)
            ->first();

        $updated = $this->suggestionService->reviewSuggestion(
            $suggestion,
            $request->input('status'),
            $request->input('review_notes'),
            $reviewer
        );

        return response()->json([
            'message' => 'Suggestion status updated.',
            'suggestion' => new EmployeeSuggestionResource($updated),
        ]);
    }
}
