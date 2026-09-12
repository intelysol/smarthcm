<?php

namespace App\Domains\Career\Http\Controllers;

use App\Domains\Career\Models\CareerSkill;
use App\Domains\Career\Models\CareerSkillGap;
use App\Domains\Career\Models\EmployeeSkill;
use App\Domains\Career\Requests\EmployeeSkillAddRequest;
use App\Domains\Career\Resources\CareerSkillGapResource;
use App\Domains\Career\Resources\EmployeeSkillResource;
use App\Domains\Career\Services\CareerSkillService;
use App\Domains\Employee\Models\Employee;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeSkillsDashboardController extends Controller
{
    public function __construct(protected CareerSkillService $skillService) {}

    public function index(Request $request): JsonResponse
    {
        $employee = Employee::query()->where('user_id', $request->user()->id)->firstOrFail();

        $skills = EmployeeSkill::query()
            ->where('tenant_id', $employee->tenant_id)
            ->where('employee_id', $employee->id)
            ->with(['skill.category', 'evidence'])
            ->get();

        $gaps = CareerSkillGap::query()
            ->where('tenant_id', $employee->tenant_id)
            ->where('employee_id', $employee->id)
            ->where('status', 'open')
            ->with('skill')
            ->get();

        return response()->json([
            'skills' => EmployeeSkillResource::collection($skills),
            'open_gaps' => CareerSkillGapResource::collection($gaps),
            'summary' => [
                'total_skills' => $skills->count(),
                'verified_skills' => $skills->where('verification_status', 'manager_verified')->count(),
                'gaps_count' => $gaps->count(),
            ]
        ]);
    }

    public function store(EmployeeSkillAddRequest $request): JsonResponse
    {
        $employee = Employee::query()->where('user_id', $request->user()->id)->firstOrFail();
        $skill = CareerSkill::query()
            ->where('tenant_id', $employee->tenant_id)
            ->findOrFail($request->validated('skill_id'));

        $employeeSkill = $this->skillService->addSkillToEmployee(
            $employee,
            $skill,
            (int) $request->validated('current_level', 1),
            $request->validated('target_level') ? (int) $request->validated('target_level') : null,
            $request->validated('source', 'employee'),
            $request->validated('notes')
        );

        return response()->json([
            'data' => new EmployeeSkillResource($employeeSkill->load(['skill', 'evidence']))
        ], 201);
    }

    public function attachEvidence(Request $request, string $employeeSkillId): JsonResponse
    {
        $employee = Employee::query()->where('user_id', $request->user()->id)->firstOrFail();
        $employeeSkill = EmployeeSkill::query()
            ->where('tenant_id', $employee->tenant_id)
            ->where('employee_id', $employee->id)
            ->findOrFail($employeeSkillId);

        $validated = $request->validate([
            'evidence_type' => ['required', 'string', 'in:certification,course,project,performance_review,assessment,manager_validation,work_experience'],
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'document_id' => ['nullable', 'string'],
            'reference_type' => ['nullable', 'string'],
            'reference_id' => ['nullable', 'string'],
        ]);

        $evidence = $this->skillService->attachEvidence(
            $employeeSkill,
            $validated['evidence_type'],
            $validated['title'],
            $validated['description'] ?? null,
            $validated['document_id'] ?? null,
            $validated['reference_type'] ?? null,
            $validated['reference_id'] ?? null
        );

        return response()->json(['data' => $evidence], 201);
    }
}
