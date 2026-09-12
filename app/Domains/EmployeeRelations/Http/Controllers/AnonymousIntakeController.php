<?php

namespace App\Domains\EmployeeRelations\Http\Controllers;

use App\Domains\EmployeeRelations\Models\EmployeeRelationCaseNote;
use App\Domains\EmployeeRelations\Requests\AnonymousReportRequest;
use App\Domains\EmployeeRelations\Services\EmployeeRelationCaseService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnonymousIntakeController extends Controller
{
    public function __construct(
        protected EmployeeRelationCaseService $caseService
    ) {}

    public function submit(AnonymousReportRequest $request): JsonResponse
    {
        $result = $this->caseService->submitAnonymousReport(
            $request->validated('tenant_id'),
            $request->validated()
        );

        return response()->json([
            'message' => 'Anonymous report submitted securely. Please keep your follow-up token safe.',
            'case_number' => $result['case_number'],
            'token' => $result['token'],
            'status' => 'submitted',
        ], 201);
    }

    public function track(Request $request, string $token): JsonResponse
    {
        $case = $this->caseService->resolveAnonymousToken($token);

        if (! $case) {
            return response()->json([
                'error' => 'Invalid or expired anonymous follow-up token.',
            ], 404);
        }

        // Return only safe anonymous-facing summary (No investigator notes or internal details)
        return response()->json([
            'case_number' => $case->case_number,
            'title' => $case->title,
            'status' => $case->status,
            'opened_at' => $case->opened_at->toDateString(),
            'target_resolution_date' => $case->target_resolution_date?->toDateString(),
            'is_closed' => $case->closed_at !== null,
        ]);
    }

    public function postMessage(Request $request, string $token): JsonResponse
    {
        $request->validate(['message' => ['required', 'string']]);

        $case = $this->caseService->resolveAnonymousToken($token);

        if (! $case) {
            return response()->json([
                'error' => 'Invalid or expired anonymous follow-up token.',
            ], 404);
        }

        EmployeeRelationCaseNote::query()->create([
            'tenant_id' => $case->tenant_id,
            'case_id' => $case->id,
            'author_id' => $case->created_by,
            'note_type' => 'internal_hr_note',
            'visibility' => 'case_team',
            'content' => "Message from Anonymous Reporter: " . $request->input('message'),
        ]);

        return response()->json([
            'message' => 'Your message has been securely forwarded to the case investigation team.',
        ], 201);
    }
}
