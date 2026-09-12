<?php

namespace App\Domains\EmployeeRelations\Http\Controllers;

use App\Domains\EmployeeRelations\Models\EmployeeRelationCase;
use App\Domains\EmployeeRelations\Models\EmployeeRelationEvidence;
use App\Domains\EmployeeRelations\Requests\EvidenceRequest;
use App\Domains\EmployeeRelations\Resources\EmployeeRelationEvidenceResource;
use App\Domains\EmployeeRelations\Services\CaseAuthorizationService;
use App\Domains\EmployeeRelations\Services\EvidenceService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EvidenceController extends Controller
{
    public function __construct(
        protected EvidenceService $evidenceService,
        protected CaseAuthorizationService $authService
    ) {}

    protected function authorizeCase(Request $request, EmployeeRelationCase $case): void
    {
        abort_unless($this->authService->canViewCase($request->user(), $case), 404);
    }

    public function index(Request $request, EmployeeRelationCase $case): JsonResponse
    {
        $this->authorizeCase($request, $case);

        $evidence = $case->evidence()->with('collector')->get()
            ->filter(fn ($item) => $this->authService->canViewEvidence($request->user(), $case, $item))
            ->values();

        return response()->json(EmployeeRelationEvidenceResource::collection($evidence));
    }

    public function store(EvidenceRequest $request, EmployeeRelationCase $case): JsonResponse
    {
        $this->authorizeCase($request, $case);
        abort_unless($this->authService->canViewEvidence($request->user(), $case), 403);

        $evidence = $this->evidenceService->storeEvidence(
            $case,
            $request->user(),
            $request->validated(),
            $request->file('file')
        );

        return response()->json([
            'message' => 'Evidence stored and SHA-256 integrity calculated.',
            'evidence' => new EmployeeRelationEvidenceResource($evidence),
        ], 201);
    }

    public function show(Request $request, EmployeeRelationCase $case, EmployeeRelationEvidence $evidence): JsonResponse
    {
        $this->authorizeCase($request, $case);
        abort_unless($this->authService->canViewEvidence($request->user(), $case, $evidence), 403);

        $this->evidenceService->logHistory($evidence, 'viewed', $request->user());

        return response()->json(new EmployeeRelationEvidenceResource($evidence->load('collector')));
    }

    public function history(Request $request, EmployeeRelationCase $case, EmployeeRelationEvidence $evidence): JsonResponse
    {
        $this->authorizeCase($request, $case);
        abort_unless($this->authService->canViewEvidence($request->user(), $case, $evidence), 403);

        return response()->json($evidence->history()->with('user')->latest()->get());
    }

    public function download(Request $request, EmployeeRelationCase $case, EmployeeRelationEvidence $evidence)
    {
        $this->authorizeCase($request, $case);
        abort_unless($this->authService->canViewEvidence($request->user(), $case, $evidence), 403);

        $this->evidenceService->logHistory($evidence, 'downloaded', $request->user());

        if ($evidence->file_path && Storage::disk(config('filesystems.default', 'local'))->exists($evidence->file_path)) {
            return Storage::disk(config('filesystems.default', 'local'))->download($evidence->file_path, $evidence->title);
        }

        return response()->json([
            'message' => 'Evidence download logged.',
            'evidence' => new EmployeeRelationEvidenceResource($evidence),
        ]);
    }

    public function dispose(Request $request, EmployeeRelationCase $case, EmployeeRelationEvidence $evidence): JsonResponse
    {
        $this->authorizeCase($request, $case);
        abort_unless($request->user()->can('hcm.employee_relations.retention.manage'), 403);

        $request->validate(['reason' => ['required', 'string']]);

        $disposed = $this->evidenceService->disposeEvidence($evidence, $request->user(), $request->input('reason'));

        return response()->json([
            'message' => 'Evidence marked as disposed.',
            'evidence' => new EmployeeRelationEvidenceResource($disposed),
        ]);
    }
}
