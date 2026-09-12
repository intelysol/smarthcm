<?php

namespace App\Domains\Learning\Http\Controllers;

use App\Domains\Employee\Models\Employee;
use App\Domains\Learning\Models\EmployeeLearningRecord;
use App\Domains\Learning\Models\LearningAssessment;
use App\Domains\Learning\Models\LearningCertificate;
use App\Domains\Learning\Models\LearningCourse;
use App\Domains\Learning\Models\LearningEnrollment;
use App\Domains\Learning\Models\LearningItem;
use App\Domains\Learning\Models\LearningNomination;
use App\Domains\Learning\Models\LearningPath;
use App\Domains\Learning\Models\LearningProgram;
use App\Domains\Learning\Models\LearningRequirement;
use App\Domains\Learning\Models\LearningRequirementAssignment;
use App\Domains\Learning\Models\LearningSession;
use App\Domains\Learning\Services\LearningAssessmentService;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class LearningUIController extends Controller
{
    /** Employee Self-Service Dashboard */
    public function employeeDashboard(Request $request): View
    {
        $employee = Employee::query()->where('user_id', $request->user()->id)->first();
        $enrollments = $employee
            ? LearningEnrollment::query()->where('employee_id', $employee->id)->with('course')->get()
            : collect();
        $mandatory = $employee
            ? LearningRequirementAssignment::query()->where('employee_id', $employee->id)->with('course')->get()
            : collect();
        $certificates = $employee
            ? LearningCertificate::query()->where('employee_id', $employee->id)->with('course')->get()
            : collect();

        return view('learning.employee.dashboard', compact('employee', 'enrollments', 'mandatory', 'certificates'));
    }

    /** Employee Catalog */
    public function employeeCatalog(Request $request): View
    {
        $tenantId = (string) $request->user()->tenant_id;
        $courses = LearningCourse::query()
            ->where('tenant_id', $tenantId)
            ->whereIn('status', ['published', 'active'])
            ->with(['category', 'provider'])
            ->paginate(12);

        return view('learning.employee.catalog', compact('courses'));
    }

    /** Employee Course Detail */
    public function employeeCourseDetail(Request $request, LearningCourse $course): View
    {
        $course->load(['category', 'provider', 'objectives', 'prerequisites', 'modules.lessons', 'sessions']);
        return view('learning.employee.course_detail', compact('course'));
    }

    /** Employee Course Player */
    public function employeePlayer(Request $request, LearningItem $item): View
    {
        $item->load(['course', 'module', 'content', 'assessment']);
        return view('learning.employee.player', compact('item'));
    }

    /** Employee Assessment Player */
    public function employeeAssessment(Request $request, LearningAssessment $assessment, LearningAssessmentService $service): View
    {
        $sanitizedQuestions = $service->getSanitizedQuestions($assessment);
        return view('learning.employee.assessment', compact('assessment', 'sanitizedQuestions'));
    }

    /** Employee Transcript */
    public function employeeTranscript(Request $request): View
    {
        $employee = Employee::query()->where('user_id', $request->user()->id)->first();
        $records = $employee
            ? EmployeeLearningRecord::query()->where('employee_id', $employee->id)->with(['course', 'certificate'])->get()
            : collect();

        return view('learning.employee.transcript', compact('employee', 'records'));
    }

    /** Manager Learning Dashboard */
    public function managerDashboard(Request $request): View
    {
        $manager = Employee::query()->where('user_id', $request->user()->id)->first();
        $team = $manager
            ? Employee::query()->where('reporting_to_id', $manager->id)->with(['learningEnrollments.course'])->get()
            : collect();

        return view('learning.manager.dashboard', compact('manager', 'team'));
    }

    /** Manager Nominations View */
    public function managerNominations(Request $request): View
    {
        $manager = Employee::query()->where('user_id', $request->user()->id)->first();
        $nominations = $manager
            ? LearningNomination::query()->where('nominated_by', $manager->id)->with(['employee', 'course'])->get()
            : collect();

        return view('learning.manager.nominations', compact('manager', 'nominations'));
    }

    /** Admin Executive Dashboard */
    public function adminDashboard(Request $request): View
    {
        $tenantId = (string) $request->user()->tenant_id;
        $courseCount = LearningCourse::query()->where('tenant_id', $tenantId)->count();
        $enrollmentCount = LearningEnrollment::query()->where('tenant_id', $tenantId)->count();
        $certCount = LearningCertificate::query()->where('tenant_id', $tenantId)->count();
        $recentCourses = LearningCourse::query()->where('tenant_id', $tenantId)->latest()->limit(5)->get();

        return view('learning.admin.dashboard', compact('courseCount', 'enrollmentCount', 'certCount', 'recentCourses'));
    }

    /** Admin Courses List */
    public function adminCourses(Request $request): View
    {
        $tenantId = (string) $request->user()->tenant_id;
        $courses = LearningCourse::query()->where('tenant_id', $tenantId)->with(['category', 'provider'])->paginate(15);
        return view('learning.admin.courses', compact('courses'));
    }

    /** Admin Course Builder */
    public function adminCourseBuilder(Request $request, LearningCourse $course): View
    {
        $course->load(['modules.lessons.items', 'objectives', 'prerequisites', 'assessments']);
        return view('learning.admin.course_builder', compact('course'));
    }

    /** Admin Sessions Management */
    public function adminSessions(Request $request): View
    {
        $tenantId = (string) $request->user()->tenant_id;
        $sessions = LearningSession::query()->where('tenant_id', $tenantId)->with(['course', 'venue', 'instructor'])->paginate(15);
        return view('learning.admin.sessions', compact('sessions'));
    }

    /** Admin Enrollments Management */
    public function adminEnrollments(Request $request): View
    {
        $tenantId = (string) $request->user()->tenant_id;
        $enrollments = LearningEnrollment::query()->where('tenant_id', $tenantId)->with(['employee', 'course'])->paginate(15);
        return view('learning.admin.enrollments', compact('enrollments'));
    }

    /** Admin Requirements & Compliance */
    public function adminRequirements(Request $request): View
    {
        $tenantId = (string) $request->user()->tenant_id;
        $requirements = LearningRequirement::query()->where('tenant_id', $tenantId)->with('course')->paginate(15);
        return view('learning.admin.requirements', compact('requirements'));
    }

    public function adminCompliance(Request $request): View
    {
        $tenantId = (string) $request->user()->tenant_id;
        $assignments = LearningRequirementAssignment::query()->where('tenant_id', $tenantId)->with(['employee', 'course'])->paginate(20);
        return view('learning.admin.compliance', compact('assignments'));
    }

    /** Admin Reports */
    public function adminReports(Request $request): View
    {
        return view('learning.admin.reports');
    }
}
