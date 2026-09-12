<?php

namespace App\Domains\Career\Http\Controllers;

use App\Domains\Career\Enums\NineBoxPosition;
use App\Domains\Career\Models\CareerPath;
use App\Domains\Career\Models\CareerPlan;
use App\Domains\Career\Models\CareerSkill;
use App\Domains\Career\Models\EmployeeCareerAspiration;
use App\Domains\Career\Models\EmployeeSkill;
use App\Domains\Career\Models\SuccessionPlan;
use App\Domains\Career\Models\SuccessionPosition;
use App\Domains\Career\Models\TalentPool;
use App\Domains\Career\Models\TalentReviewRecord;
use App\Domains\Career\Services\CareerJobMatchingService;
use App\Domains\Career\Services\CareerTalentAnalyticsService;
use App\Domains\Career\Services\SuccessionRiskEngine;
use App\Domains\Employee\Models\Employee;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CareerUIController extends Controller
{
    public function __construct(
        protected CareerTalentAnalyticsService $analyticsService,
        protected SuccessionRiskEngine $riskEngine,
        protected CareerJobMatchingService $matchingService
    ) {}

    // ESS Views
    public function employeeSkills(Request $request): View
    {
        $employee = Employee::query()->where('user_id', $request->user()->id)->first();
        $skills = $employee ? EmployeeSkill::query()->where('employee_id', $employee->id)->with(['skill.category', 'evidence'])->get() : collect();
        $allSkills = $employee ? CareerSkill::query()->where('tenant_id', $employee->tenant_id)->get() : collect();

        return view('career.employee.skills', compact('employee', 'skills', 'allSkills'));
    }

    public function employeeCareerDashboard(Request $request): View
    {
        $employee = Employee::query()->where('user_id', $request->user()->id)->first();
        $aspiration = $employee ? EmployeeCareerAspiration::query()->where('employee_id', $employee->id)->with('targetJob')->first() : null;
        $activePlan = $employee ? CareerPlan::query()->where('employee_id', $employee->id)->with(['targetJob', 'actions'])->latest()->first() : null;
        $matchingOpportunities = $employee ? $this->matchingService->findMatchingJobsForEmployee($employee, 3) : collect();

        return view('career.employee.career_dashboard', compact('employee', 'aspiration', 'activePlan', 'matchingOpportunities'));
    }

    public function employeeCareerPath(Request $request): View
    {
        $employee = Employee::query()->where('user_id', $request->user()->id)->first();
        $path = $employee ? CareerPath::query()->where('tenant_id', $employee->tenant_id)->with(['steps.job'])->first() : null;

        return view('career.employee.career_path', compact('employee', 'path'));
    }

    public function employeeCareerPlans(Request $request): View
    {
        $employee = Employee::query()->where('user_id', $request->user()->id)->first();
        $plans = $employee ? CareerPlan::query()->where('employee_id', $employee->id)->with(['targetJob', 'actions'])->latest()->get() : collect();

        return view('career.employee.career_plan', compact('employee', 'plans'));
    }

    public function employeeMentoring(Request $request): View
    {
        $employee = Employee::query()->where('user_id', $request->user()->id)->first();
        return view('career.employee.mentoring', compact('employee'));
    }

    // MSS Views
    public function managerDashboard(Request $request): View
    {
        $manager = Employee::query()->where('user_id', $request->user()->id)->first();
        return view('career.manager.dashboard', compact('manager'));
    }

    public function managerTeamSkills(Request $request): View
    {
        $manager = Employee::query()->where('user_id', $request->user()->id)->first();
        $team = $manager ? Employee::query()->where('reporting_manager_id', $manager->id)->with(['skills.skill'])->get() : collect();

        return view('career.manager.team_skills', compact('manager', 'team'));
    }

    // Admin Views
    public function adminTalentDashboard(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        $stats = $this->analyticsService->generateSnapshot($tenantId);
        $pools = TalentPool::query()->where('tenant_id', $tenantId)->withCount('members')->get();
        $plans = SuccessionPlan::query()->where('tenant_id', $tenantId)->withCount('positions')->get();

        return view('career.admin.talent_dashboard', compact('stats', 'pools', 'plans'));
    }

    public function adminSkillMatrix(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        $skills = CareerSkill::query()->where('tenant_id', $tenantId)->get();
        $employees = Employee::query()->where('tenant_id', $tenantId)->with(['skills.skill', 'department'])->limit(50)->get();

        return view('career.admin.skill_matrix', compact('skills', 'employees'));
    }

    public function adminNineBox(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        $records = TalentReviewRecord::query()->where('tenant_id', $tenantId)->with(['employee.department'])->get();
        $gridPositions = NineBoxPosition::cases();

        return view('career.admin.nine_box', compact('records', 'gridPositions'));
    }

    public function adminSuccession(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        $coverage = $this->riskEngine->calculateCoverage($tenantId);
        $positions = SuccessionPosition::query()->where('tenant_id', $tenantId)->with(['position', 'job', 'incumbent', 'candidates.employee'])->get();

        return view('career.admin.succession', compact('coverage', 'positions'));
    }

    public function adminCriticalPosition(Request $request, string $positionId): View
    {
        $position = SuccessionPosition::query()->where('tenant_id', $request->user()->tenant_id)->with(['position', 'job', 'incumbent', 'candidates.employee', 'candidates.developmentActions'])->findOrFail($positionId);

        return view('career.admin.critical_position', compact('position'));
    }

    public function adminTalentPools(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        $pools = TalentPool::query()->where('tenant_id', $tenantId)->with(['members.employee'])->get();

        return view('career.admin.talent_pools', compact('pools'));
    }

    public function adminReports(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        $stats = $this->analyticsService->generateSnapshot($tenantId);

        return view('career.admin.reports', compact('stats'));
    }
}
