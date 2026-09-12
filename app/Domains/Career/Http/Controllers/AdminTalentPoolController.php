<?php

namespace App\Domains\Career\Http\Controllers;

use App\Domains\Career\Models\TalentPool;
use App\Domains\Career\Resources\TalentPoolResource;
use App\Domains\Career\Services\TalentPoolService;
use App\Domains\Employee\Models\Employee;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminTalentPoolController extends Controller
{
    public function __construct(protected TalentPoolService $poolService) {}

    public function index(Request $request): JsonResponse
    {
        $pools = TalentPool::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->withCount('members')
            ->get();

        return response()->json(TalentPoolResource::collection($pools));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'criteria' => ['nullable', 'array'],
        ]);

        $pool = $this->poolService->createPool(
            $request->user()->tenant_id,
            $validated['code'],
            $validated['name'],
            $validated['description'] ?? null,
            $validated['criteria'] ?? null
        );

        return response()->json(['data' => new TalentPoolResource($pool)], 201);
    }

    public function members(Request $request, string $poolId): JsonResponse
    {
        $pool = TalentPool::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->findOrFail($poolId);

        $members = $pool->members()->with(['employee.department', 'employee.designation'])->paginate(20);
        return response()->json($members);
    }

    public function addMember(Request $request, string $poolId): JsonResponse
    {
        $pool = TalentPool::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->findOrFail($poolId);

        $validated = $request->validate([
            'employee_id' => ['required', 'string', 'exists:employees,id'],
            'reason' => ['nullable', 'string'],
        ]);

        $employee = Employee::query()
            ->where('tenant_id', $pool->tenant_id)
            ->findOrFail($validated['employee_id']);

        $member = $this->poolService->addMember(
            $pool,
            $employee,
            null,
            $validated['reason'] ?? null
        );

        return response()->json(['data' => $member->load('employee')], 201);
    }

    public function removeMember(Request $request, string $poolId, string $employeeId): JsonResponse
    {
        $pool = TalentPool::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->findOrFail($poolId);

        $employee = Employee::query()
            ->where('tenant_id', $pool->tenant_id)
            ->findOrFail($employeeId);

        $this->poolService->removeMember($pool, $employee);
        return response()->json(['message' => 'Member removed successfully.'], 200);
    }
}
