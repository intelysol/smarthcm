<?php

namespace App\Domains\Offboarding\Http\Controllers;

use App\Domains\Offboarding\Models\SeparationHandoverRecord;
use App\Domains\Offboarding\Models\SeparationRequest;
use App\Domains\Offboarding\Services\SeparationHandoverService;
use App\Domains\Offboarding\Services\SeparationSecurityService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SeparationHandoverController extends Controller
{
    public function __construct(
        protected SeparationHandoverService $handoverService,
        protected SeparationSecurityService $securityService
    ) {
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $separation = SeparationRequest::with('handoverRecord.items')->findOrFail($id);
        $this->securityService->authorizeRequestAccess($request->user(), $separation);

        return response()->json($separation->handoverRecord);
    }

    public function store(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'successor_employee_id' => 'nullable|uuid',
            'handover_date' => 'nullable|date',
            'handover_notes' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.title' => 'required|string|max:150',
            'items.*.category' => 'nullable|string',
            'items.*.notes' => 'nullable|string',
        ]);

        $separation = SeparationRequest::findOrFail($id);
        $this->securityService->authorizeRequestAccess($request->user(), $separation);

        $record = $this->handoverService->createHandoverRecord($separation, $validated);
        return response()->json($record, 201);
    }

    public function verify(Request $request, string $recordId): JsonResponse
    {
        $record = SeparationHandoverRecord::with('request')->findOrFail($recordId);
        $this->securityService->authorizeRequestAccess($request->user(), $record->request);

        $verified = $this->handoverService->verifyHandover($record, $request->user());
        return response()->json($verified);
    }
}
