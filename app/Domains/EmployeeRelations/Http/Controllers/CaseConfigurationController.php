<?php

namespace App\Domains\EmployeeRelations\Http\Controllers;

use App\Domains\EmployeeRelations\Models\EmployeeRelationCase;
use App\Domains\EmployeeRelations\Models\EmployeeRelationCaseType;
use App\Domains\EmployeeRelations\Models\EmployeeRelationLegalHold;
use App\Domains\EmployeeRelations\Models\EmployeeRelationPolicyReference;
use App\Domains\EmployeeRelations\Models\EmployeeRelationRetentionPolicy;
use App\Domains\EmployeeRelations\Requests\LegalHoldRequest;
use App\Domains\EmployeeRelations\Services\RetentionAndLegalHoldService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CaseConfigurationController extends Controller
{
    public function __construct(
        protected RetentionAndLegalHoldService $retentionService
    ) {}

    public function getCaseTypes(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $caseTypes = EmployeeRelationCaseType::query()
            ->where(function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId)->orWhereNull('tenant_id');
            })
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->get();

        return response()->json($caseTypes);
    }

    public function storeCaseType(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('hcm.employee_relations.manage'), 403);

        $request->validate([
            'code' => ['required', 'string', 'max:60'],
            'name' => ['required', 'string', 'max:120'],
            'category' => ['nullable', 'string', 'max:60'],
            'default_priority' => ['nullable', 'string'],
            'default_severity' => ['nullable', 'string'],
            'default_confidentiality' => ['nullable', 'string'],
            'is_anonymous_allowed' => ['nullable', 'boolean'],
            'is_self_service_allowed' => ['nullable', 'boolean'],
        ]);

        $type = EmployeeRelationCaseType::query()->create([
            'tenant_id' => $request->user()->tenant_id,
            ...$request->all(),
        ]);

        return response()->json(['message' => 'Case type created.', 'case_type' => $type], 201);
    }

    public function getPolicyReferences(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $policies = EmployeeRelationPolicyReference::query()
            ->where(function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId)->orWhereNull('tenant_id');
            })
            ->where('is_active', true)
            ->get();

        return response()->json($policies);
    }

    public function storePolicyReference(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('hcm.employee_relations.manage'), 403);

        $request->validate([
            'policy_name' => ['required', 'string', 'max:200'],
            'policy_code' => ['nullable', 'string', 'max:60'],
            'category' => ['nullable', 'string'],
            'section_clause' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
        ]);

        $policy = EmployeeRelationPolicyReference::query()->create([
            'tenant_id' => $request->user()->tenant_id,
            ...$request->all(),
        ]);

        return response()->json(['message' => 'Policy reference created.', 'policy' => $policy], 201);
    }

    public function getRetentionPolicies(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $retention = EmployeeRelationRetentionPolicy::query()
            ->where(function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId)->orWhereNull('tenant_id');
            })
            ->get();

        return response()->json($retention);
    }

    public function storeRetentionPolicy(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('hcm.employee_relations.retention.manage'), 403);

        $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'retention_years' => ['required', 'integer', 'min:1'],
            'case_type_id' => ['nullable', 'string', 'uuid'],
            'legal_basis' => ['nullable', 'string'],
        ]);

        $policy = EmployeeRelationRetentionPolicy::query()->create([
            'tenant_id' => $request->user()->tenant_id,
            ...$request->all(),
        ]);

        return response()->json(['message' => 'Retention policy created.', 'policy' => $policy], 201);
    }

    public function getLegalHolds(Request $request, EmployeeRelationCase $case): JsonResponse
    {
        abort_unless($request->user()->can('hcm.employee_relations.legal_hold.manage'), 403);

        return response()->json($case->legalHolds()->with('placedByUser')->get());
    }

    public function placeLegalHold(LegalHoldRequest $request, EmployeeRelationCase $case): JsonResponse
    {
        abort_unless($request->user()->can('hcm.employee_relations.legal_hold.manage'), 403);

        $hold = $this->retentionService->placeLegalHold(
            $case,
            $request->user(),
            $request->validated('reason'),
            $request->validated('hold_reference')
        );

        return response()->json(['message' => 'Legal hold placed successfully.', 'legal_hold' => $hold], 201);
    }

    public function releaseLegalHold(Request $request, EmployeeRelationCase $case, EmployeeRelationLegalHold $hold): JsonResponse
    {
        abort_unless($request->user()->can('hcm.employee_relations.legal_hold.manage'), 403);

        $request->validate(['release_reason' => ['required', 'string']]);

        $released = $this->retentionService->releaseLegalHold(
            $hold,
            $request->user(),
            $request->input('release_reason')
        );

        return response()->json(['message' => 'Legal hold released.', 'legal_hold' => $released]);
    }

    public function disposeCase(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('hcm.employee_relations.retention.manage'), 403);

        $request->validate([
            'case_id' => ['required', 'string', 'uuid'],
            'reason' => ['required', 'string'],
        ]);

        $case = EmployeeRelationCase::query()->where('tenant_id', $request->user()->tenant_id)->findOrFail($request->input('case_id'));

        $this->retentionService->disposeCase($case, $request->user(), $request->input('reason'));

        return response()->json(['message' => 'Case archived and disposed according to retention policy.']);
    }
}
