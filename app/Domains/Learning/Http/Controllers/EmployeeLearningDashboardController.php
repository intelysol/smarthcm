<?php

namespace App\Domains\Learning\Http\Controllers;

use App\Domains\Employee\Models\Employee;
use App\Domains\Learning\Models\EmployeeLearningRecord;
use App\Domains\Learning\Models\LearningCertificate;
use App\Domains\Learning\Models\LearningCredit;
use App\Domains\Learning\Models\LearningEnrollment;
use App\Domains\Learning\Models\LearningRecommendation;
use App\Domains\Learning\Models\LearningRequirementAssignment;
use App\Domains\Learning\Resources\EmployeeLearningRecordResource;
use App\Domains\Learning\Resources\LearningCertificateResource;
use App\Domains\Learning\Resources\LearningEnrollmentResource;
use App\Domains\Learning\Resources\LearningRequirementResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeLearningDashboardController extends Controller
{
    public function dashboard(Request $request): JsonResponse
    {
        $employee = Employee::query()->where('user_id', $request->user()->id)->firstOrFail();

        $inProgressEnrollments = LearningEnrollment::query()
            ->where('tenant_id', $employee->tenant_id)
            ->where('employee_id', $employee->id)
            ->whereIn('status', ['enrolled', 'in_progress'])
            ->with(['course', 'session'])
            ->get();

        $completedCount = LearningEnrollment::query()
            ->where('tenant_id', $employee->tenant_id)
            ->where('employee_id', $employee->id)
            ->where('status', 'completed')
            ->count();

        $mandatoryAssignments = LearningRequirementAssignment::query()
            ->where('tenant_id', $employee->tenant_id)
            ->where('employee_id', $employee->id)
            ->whereIn('status', ['assigned', 'enrolled', 'in_progress', 'overdue'])
            ->with(['course', 'requirement'])
            ->get();

        $overdueCount = $mandatoryAssignments->where('status', 'overdue')->count();

        $credits = (float) (LearningCredit::query()
            ->where('tenant_id', $employee->tenant_id)
            ->where('employee_id', $employee->id)
            ->value('total_earned') ?? 0.0);

        $certificatesCount = LearningCertificate::query()
            ->where('tenant_id', $employee->tenant_id)
            ->where('employee_id', $employee->id)
            ->where('status', 'active')
            ->count();

        $recommendations = LearningRecommendation::query()
            ->where('tenant_id', $employee->tenant_id)
            ->where('employee_id', $employee->id)
            ->where('status', 'pending')
            ->with(['course', 'program', 'path'])
            ->get();

        return response()->json([
            'data' => [
                'employee' => [
                    'id' => $employee->id,
                    'name' => "{$employee->first_name} {$employee->last_name}",
                ],
                'summary' => [
                    'in_progress_count' => $inProgressEnrollments->count(),
                    'completed_count' => $completedCount,
                    'mandatory_count' => $mandatoryAssignments->count(),
                    'overdue_count' => $overdueCount,
                    'total_credits' => $credits,
                    'certificates_count' => $certificatesCount,
                ],
                'in_progress' => LearningEnrollmentResource::collection($inProgressEnrollments),
                'mandatory_training' => $mandatoryAssignments,
                'recommendations' => $recommendations,
            ]
        ]);
    }

    public function transcript(Request $request): JsonResponse
    {
        $employee = Employee::query()->where('user_id', $request->user()->id)->firstOrFail();

        $records = EmployeeLearningRecord::query()
            ->where('tenant_id', $employee->tenant_id)
            ->where('employee_id', $employee->id)
            ->with(['course', 'certificate', 'provider'])
            ->orderBy('completion_date', 'desc')
            ->get();

        $totalHours = (float) $records->sum('learning_hours');
        $totalCredits = (float) $records->sum('credits_awarded');

        return response()->json([
            'data' => [
                'total_courses_completed' => $records->count(),
                'total_learning_hours' => $totalHours,
                'total_credits' => $totalCredits,
                'records' => EmployeeLearningRecordResource::collection($records),
            ]
        ]);
    }

    public function requirements(Request $request): JsonResponse
    {
        $employee = Employee::query()->where('user_id', $request->user()->id)->firstOrFail();

        $assignments = LearningRequirementAssignment::query()
            ->where('tenant_id', $employee->tenant_id)
            ->where('employee_id', $employee->id)
            ->with(['course', 'requirement'])
            ->orderBy('due_at')
            ->get();

        return response()->json([
            'data' => $assignments
        ]);
    }

    public function certificates(Request $request): JsonResponse
    {
        $employee = Employee::query()->where('user_id', $request->user()->id)->firstOrFail();

        $certificates = LearningCertificate::query()
            ->where('tenant_id', $employee->tenant_id)
            ->where('employee_id', $employee->id)
            ->with(['course', 'version'])
            ->orderBy('issued_at', 'desc')
            ->get();

        return LearningCertificateResource::collection($certificates)->response();
    }
}
