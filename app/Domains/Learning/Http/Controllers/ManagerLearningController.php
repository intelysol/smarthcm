<?php

namespace App\Domains\Learning\Http\Controllers;

use App\Domains\Employee\Models\Employee;
use App\Domains\Learning\Events\LearningNominationCreated;
use App\Domains\Learning\Models\LearningEnrollment;
use App\Domains\Learning\Models\LearningNomination;
use App\Domains\Learning\Models\LearningRequirementAssignment;
use App\Domains\Learning\Requests\NominationRequest;
use App\Domains\Learning\Resources\LearningEnrollmentResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ManagerLearningController extends Controller
{
    public function dashboard(Request $request): JsonResponse
    {
        $manager = Employee::query()->where('user_id', $request->user()->id)->firstOrFail();
        $teamMemberIds = Employee::query()
            ->where('tenant_id', $manager->tenant_id)
            ->where('reporting_manager_id', $manager->id)
            ->pluck('id')
            ->all();

        $teamEnrollments = LearningEnrollment::query()
            ->where('tenant_id', $manager->tenant_id)
            ->whereIn('employee_id', $teamMemberIds)
            ->with(['employee', 'course'])
            ->get();

        $overdueMandatory = LearningRequirementAssignment::query()
            ->where('tenant_id', $manager->tenant_id)
            ->whereIn('employee_id', $teamMemberIds)
            ->where('status', 'overdue')
            ->with(['employee', 'course'])
            ->get();

        $nominations = LearningNomination::query()
            ->where('tenant_id', $manager->tenant_id)
            ->where('nominated_by', $manager->id)
            ->with(['employee', 'course'])
            ->get();

        return response()->json([
            'data' => [
                'team_members_count' => count($teamMemberIds),
                'active_enrollments_count' => $teamEnrollments->whereIn('status', ['enrolled', 'in_progress'])->count(),
                'completed_courses_count' => $teamEnrollments->where('status', 'completed')->count(),
                'overdue_mandatory_count' => $overdueMandatory->count(),
                'recent_team_enrollments' => LearningEnrollmentResource::collection($teamEnrollments->take(10)),
                'overdue_assignments' => $overdueMandatory,
                'nominations' => $nominations,
            ]
        ]);
    }

    public function team(Request $request): JsonResponse
    {
        $manager = Employee::query()->where('user_id', $request->user()->id)->firstOrFail();
        $teamMembers = Employee::query()
            ->where('tenant_id', $manager->tenant_id)
            ->where('reporting_manager_id', $manager->id)
            ->with(['learningRecords.course', 'learningEnrollments.course'])
            ->get();

        return response()->json([
            'data' => $teamMembers->map(fn ($emp) => [
                'id' => $emp->id,
                'name' => "{$emp->first_name} {$emp->last_name}",
                'employee_number' => $emp->employee_number,
                'active_enrollments' => $emp->learningEnrollments->whereIn('status', ['enrolled', 'in_progress'])->values(),
                'completed_records' => $emp->learningRecords,
            ])
        ]);
    }

    public function nominations(Request $request): JsonResponse
    {
        $manager = Employee::query()->where('user_id', $request->user()->id)->firstOrFail();

        $nominations = LearningNomination::query()
            ->where('tenant_id', $manager->tenant_id)
            ->where('nominated_by', $manager->id)
            ->with(['employee', 'course'])
            ->paginate((int) $request->integer('per_page', 20));

        return response()->json($nominations);
    }

    public function storeNomination(NominationRequest $request): JsonResponse
    {
        $manager = Employee::query()->where('user_id', $request->user()->id)->firstOrFail();

        // Verify subordinate
        $subordinate = Employee::query()
            ->where('tenant_id', $manager->tenant_id)
            ->where('id', $request->validated('employee_id'))
            ->where('reporting_manager_id', $manager->id)
            ->firstOrFail();

        $nomination = LearningNomination::query()->create([
            'tenant_id' => $manager->tenant_id,
            'employee_id' => $subordinate->id,
            'course_id' => $request->validated('course_id'),
            'nominated_by' => $manager->id,
            'is_mandatory' => (bool) $request->validated('is_mandatory', false),
            'reason' => $request->validated('reason'),
            'status' => 'pending',
        ]);

        LearningNominationCreated::dispatch($nomination);

        return response()->json(['data' => $nomination->load(['employee', 'course'])], 201);
    }
}
