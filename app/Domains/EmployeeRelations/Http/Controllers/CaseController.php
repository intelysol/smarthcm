<?php

namespace App\Domains\EmployeeRelations\Http\Controllers;

use App\Domains\EmployeeRelations\Enums\CaseStatus;
use App\Domains\EmployeeRelations\Models\EmployeeRelationCase;
use App\Domains\EmployeeRelations\Models\EmployeeRelationCaseNote;
use App\Domains\EmployeeRelations\Models\EmployeeRelationCaseTask;
use App\Domains\EmployeeRelations\Models\EmployeeRelationCorrespondence;
use App\Domains\EmployeeRelations\Requests\CaseAssignRequest;
use App\Domains\EmployeeRelations\Requests\CaseConflictRequest;
use App\Domains\EmployeeRelations\Requests\CaseCreateRequest;
use App\Domains\EmployeeRelations\Requests\CaseNoteRequest;
use App\Domains\EmployeeRelations\Requests\CaseParticipantRequest;
use App\Domains\EmployeeRelations\Requests\CaseTriageRequest;
use App\Domains\EmployeeRelations\Requests\CaseUpdateRequest;
use App\Domains\EmployeeRelations\Resources\EmployeeRelationCaseResource;
use App\Domains\EmployeeRelations\Services\CaseAuthorizationService;
use App\Domains\EmployeeRelations\Services\EmployeeRelationAiService;
use App\Domains\EmployeeRelations\Services\EmployeeRelationCaseService;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CaseController extends Controller
{
    public function __construct(
        protected EmployeeRelationCaseService $caseService,
        protected CaseAuthorizationService $authService,
        protected EmployeeRelationAiService $aiService
    ) {}

    protected function authorizeCase(Request $request, EmployeeRelationCase $case): void
    {
        abort_unless($this->authService->canViewCase($request->user(), $case), 404);
    }

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $query = EmployeeRelationCase::query()
            ->where('tenant_id', $tenantId)
            ->with(['caseType', 'subjectEmployee']);

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->input('priority'));
        }
        if ($request->filled('severity')) {
            $query->where('severity', $request->input('severity'));
        }
        if ($request->filled('case_type_id')) {
            $query->where('case_type_id', $request->input('case_type_id'));
        }
        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('case_number', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%");
            });
        }

        $cases = $query->latest('opened_at')->get()
            ->filter(fn ($case) => $this->authService->canViewCase($request->user(), $case))
            ->values();

        return response()->json(EmployeeRelationCaseResource::collection($cases));
    }

    public function store(CaseCreateRequest $request): JsonResponse
    {
        $case = $this->caseService->createCase(
            $request->user()->tenant_id,
            $request->validated(),
            $request->user()
        );

        return response()->json([
            'message' => 'Employee Relations case created successfully.',
            'case' => new EmployeeRelationCaseResource($case),
        ], 201);
    }

    public function show(Request $request, EmployeeRelationCase $case): JsonResponse
    {
        $this->authorizeCase($request, $case);

        $case->load([
            'caseType',
            'subjectEmployee',
            'reporters',
            'triage.recommendedCaseType',
            'assignments.user',
            'participants.user',
            'conflicts.user',
            'slas',
            'legalHolds',
        ]);

        return response()->json(new EmployeeRelationCaseResource($case));
    }

    public function update(CaseUpdateRequest $request, EmployeeRelationCase $case): JsonResponse
    {
        $this->authorizeCase($request, $case);
        abort_unless($this->authService->canEditCase($request->user(), $case), 403);

        $case->update($request->validated());

        return response()->json([
            'message' => 'Case updated successfully.',
            'case' => new EmployeeRelationCaseResource($case->fresh()),
        ]);
    }

    public function triage(CaseTriageRequest $request, EmployeeRelationCase $case): JsonResponse
    {
        $this->authorizeCase($request, $case);
        abort_unless($this->authService->canEditCase($request->user(), $case), 403);

        $triage = $this->caseService->triageCase($case, $request->validated(), $request->user());

        return response()->json([
            'message' => 'Case triaged successfully.',
            'triage' => $triage,
        ]);
    }

    public function assign(CaseAssignRequest $request, EmployeeRelationCase $case): JsonResponse
    {
        $this->authorizeCase($request, $case);
        abort_unless($this->authService->canEditCase($request->user(), $case), 403);

        $targetUser = User::query()->where('tenant_id', $case->tenant_id)->findOrFail($request->validated('user_id'));
        $assignment = $this->caseService->assignUser(
            $case,
            $targetUser,
            $request->validated('role'),
            $request->user(),
            $request->validated('notes')
        );

        return response()->json([
            'message' => 'User assigned to case successfully.',
            'assignment' => $assignment,
        ], 201);
    }

    public function close(Request $request, EmployeeRelationCase $case): JsonResponse
    {
        $this->authorizeCase($request, $case);
        abort_unless($this->authService->canEditCase($request->user(), $case), 403);

        $request->validate(['reason' => ['nullable', 'string']]);

        $closed = $this->caseService->transitionState($case, CaseStatus::CLOSED->value, $request->user(), $request->input('reason'));

        return response()->json([
            'message' => 'Case closed successfully.',
            'case' => new EmployeeRelationCaseResource($closed),
        ]);
    }

    public function reopen(Request $request, EmployeeRelationCase $case): JsonResponse
    {
        $this->authorizeCase($request, $case);
        abort_unless($this->authService->canEditCase($request->user(), $case), 403);

        $request->validate(['reason' => ['required', 'string']]);

        $reopened = $this->caseService->transitionState($case, CaseStatus::ASSIGNED->value, $request->user(), $request->input('reason'));

        return response()->json([
            'message' => 'Case reopened successfully.',
            'case' => new EmployeeRelationCaseResource($reopened),
        ]);
    }

    public function getParticipants(Request $request, EmployeeRelationCase $case): JsonResponse
    {
        $this->authorizeCase($request, $case);

        return response()->json($case->participants()->with('user')->get());
    }

    public function addParticipant(CaseParticipantRequest $request, EmployeeRelationCase $case): JsonResponse
    {
        abort_unless($this->authService->canEditCase($request->user(), $case), 403);

        $participant = $case->participants()->create([
            'tenant_id' => $case->tenant_id,
            ...$request->validated(),
        ]);

        return response()->json([
            'message' => 'Participant added successfully.',
            'participant' => $participant,
        ], 201);
    }

    public function getConflicts(Request $request, EmployeeRelationCase $case): JsonResponse
    {
        $this->authorizeCase($request, $case);

        return response()->json($case->conflicts()->with('user')->get());
    }

    public function declareConflict(CaseConflictRequest $request, EmployeeRelationCase $case): JsonResponse
    {
        $conflict = $case->conflicts()->create([
            'tenant_id' => $case->tenant_id,
            'user_id' => $request->user()->id,
            'declaration' => $request->validated('declaration'),
            'reason' => $request->validated('reason'),
            'relationship_type' => $request->validated('relationship_type'),
            'status' => $request->validated('declaration') === 'conflict_exists' ? 'disqualified' : 'pending',
        ]);

        return response()->json([
            'message' => 'Conflict declaration recorded.',
            'conflict' => $conflict,
        ], 201);
    }

    public function getNotes(Request $request, EmployeeRelationCase $case): JsonResponse
    {
        $this->authorizeCase($request, $case);

        $notes = $case->caseNotes()->with('author')->get()
            ->filter(fn ($note) => $this->authService->canViewNote($request->user(), $case, $note))
            ->values();

        return response()->json($notes);
    }

    public function addNote(CaseNoteRequest $request, EmployeeRelationCase $case): JsonResponse
    {
        $this->authorizeCase($request, $case);

        $note = $case->caseNotes()->create([
            'tenant_id' => $case->tenant_id,
            'author_id' => $request->user()->id,
            'note_type' => $request->validated('note_type', 'general_note'),
            'visibility' => $request->validated('visibility', 'case_team'),
            'content' => $request->validated('content'),
        ]);

        return response()->json([
            'message' => 'Case note added.',
            'note' => $note,
        ], 201);
    }

    public function getTasks(Request $request, EmployeeRelationCase $case): JsonResponse
    {
        $this->authorizeCase($request, $case);

        return response()->json($case->tasks()->with('assignee')->get());
    }

    public function addTask(Request $request, EmployeeRelationCase $case): JsonResponse
    {
        abort_unless($this->authService->canEditCase($request->user(), $case), 403);

        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'task_type' => ['nullable', 'string'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'due_date' => ['nullable', 'date'],
        ]);

        $task = $case->tasks()->create([
            'tenant_id' => $case->tenant_id,
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'task_type' => $request->input('task_type', 'collect_evidence'),
            'assigned_to' => $request->input('assigned_to'),
            'due_date' => $request->input('due_date'),
            'status' => 'pending',
        ]);

        return response()->json(['message' => 'Task created.', 'task' => $task], 201);
    }

    public function getTimeline(Request $request, EmployeeRelationCase $case): JsonResponse
    {
        $this->authorizeCase($request, $case);

        $timeline = $this->caseService->getTimeline($case, $request->user(), $this->authService);

        return response()->json($timeline);
    }

    public function getAudit(Request $request, EmployeeRelationCase $case): JsonResponse
    {
        $this->authorizeCase($request, $case);

        $evidenceAudits = $case->evidence()->with('history.user')->get()->flatMap->history;

        return response()->json([
            'case_number' => $case->case_number,
            'access_logs' => $evidenceAudits,
        ]);
    }

    public function export(Request $request, EmployeeRelationCase $case): JsonResponse
    {
        abort_unless($this->authService->canExportCase($request->user(), $case), 403);

        $case->load(['caseType', 'allegations.findings', 'decisions', 'correctiveActions', 'appeals']);

        return response()->json([
            'bundle_type' => 'case_export_bundle',
            'exported_at' => now()->toIso8601String(),
            'case' => new EmployeeRelationCaseResource($case),
            'allegations' => $case->allegations,
            'decisions' => $case->decisions,
            'corrective_actions' => $case->correctiveActions,
            'appeals' => $case->appeals,
        ]);
    }

    public function generateAiSummary(Request $request, EmployeeRelationCase $case): JsonResponse
    {
        $this->authorizeCase($request, $case);

        $summary = $this->aiService->generateCaseSummary($case, $request->user(), $this->authService);

        return response()->json($summary);
    }
}
