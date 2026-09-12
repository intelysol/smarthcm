<?php

namespace App\Domains\Engagement\Http\Controllers;

use App\Domains\Employee\Models\Employee;
use App\Domains\Engagement\Models\CultureInitiative;
use App\Domains\Engagement\Models\EmployeeSuggestion;
use App\Domains\Engagement\Models\EngagementActionPlan;
use App\Domains\Engagement\Models\EngagementCampaign;
use App\Domains\Engagement\Models\EngagementRecognition;
use App\Domains\Engagement\Models\EngagementSurvey;
use App\Domains\Engagement\Services\EngagementAnalyticsService;
use App\Domains\Engagement\Services\EngagementResultService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EngagementUIController extends Controller
{
    public function __construct(
        protected EngagementResultService $resultService,
        protected EngagementAnalyticsService $analyticsService
    ) {}

    protected function getEmployee(Request $request): ?Employee
    {
        return Employee::query()
            ->where('tenant_id', $request->user()?->tenant_id)
            ->where('user_id', $request->user()?->id)
            ->first();
    }

    public function employeeDashboard(Request $request): View
    {
        $employee = $this->getEmployee($request);
        $tenantId = $request->user()->tenant_id;

        $activeSurveys = EngagementCampaign::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->with(['survey'])
            ->get();

        $recognitions = EngagementRecognition::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'published')
            ->latest()
            ->limit(6)
            ->get();

        $suggestions = EmployeeSuggestion::query()
            ->where('tenant_id', $tenantId)
            ->latest()
            ->limit(5)
            ->get();

        return view('engagement.employee.dashboard', compact('employee', 'activeSurveys', 'recognitions', 'suggestions'));
    }

    public function employeeTakeSurvey(Request $request, EngagementCampaign $campaign): View
    {
        $employee = $this->getEmployee($request);
        $campaign->load(['survey.sections.questions', 'survey.questions']);

        return view('engagement.employee.survey_take', compact('employee', 'campaign'));
    }

    public function employeePulse(Request $request): View
    {
        $employee = $this->getEmployee($request);
        $pulseCampaign = EngagementCampaign::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->where('status', 'active')
            ->whereHas('survey', fn ($q) => $q->where('survey_type', 'pulse'))
            ->with(['survey.questions'])
            ->first();

        return view('engagement.employee.pulse', compact('employee', 'pulseCampaign'));
    }

    public function employeeRecognition(Request $request): View
    {
        $employee = $this->getEmployee($request);
        $recognitions = EngagementRecognition::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->where('status', 'published')
            ->latest()
            ->paginate(15);

        $colleagues = Employee::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->where('id', '!=', $employee?->id)
            ->limit(50)
            ->get();

        return view('engagement.employee.recognition', compact('employee', 'recognitions', 'colleagues'));
    }

    public function employeeSuggestions(Request $request): View
    {
        $employee = $this->getEmployee($request);
        $suggestions = EmployeeSuggestion::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->latest()
            ->paginate(15);

        return view('engagement.employee.suggestions', compact('employee', 'suggestions'));
    }

    public function managerDashboard(Request $request): View
    {
        $manager = $this->getEmployee($request);
        $campaigns = EngagementCampaign::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->whereIn('status', ['active', 'closed'])
            ->with(['survey'])
            ->get();

        $actionPlans = EngagementActionPlan::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->where('owner_id', $manager?->id)
            ->with('items')
            ->get();

        return view('engagement.manager.dashboard', compact('manager', 'campaigns', 'actionPlans'));
    }

    public function managerTeamResults(Request $request, EngagementCampaign $campaign): View
    {
        $manager = $this->getEmployee($request);
        $results = $this->resultService->getCampaignResults($campaign, $manager?->department_id);

        return view('engagement.manager.team_results', compact('manager', 'campaign', 'results'));
    }

    public function adminDashboard(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        $campaigns = EngagementCampaign::query()->where('tenant_id', $tenantId)->latest()->get();
        $metrics = $this->analyticsService->generateEngagementMetrics($tenantId);

        return view('engagement.admin.dashboard', compact('campaigns', 'metrics'));
    }

    public function adminSurveys(Request $request): View
    {
        $surveys = EngagementSurvey::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->withCount(['sections', 'questions'])
            ->get();

        return view('engagement.admin.surveys', compact('surveys'));
    }

    public function adminSurveyBuilder(Request $request, EngagementSurvey $survey): View
    {
        $survey->load(['sections.questions', 'questions', 'rules']);

        return view('engagement.admin.survey_builder', compact('survey'));
    }

    public function adminCampaignResults(Request $request, EngagementCampaign $campaign): View
    {
        $results = $this->resultService->getCampaignResults($campaign);
        $nps = $this->resultService->calculateNps($campaign);
        $dimensions = $this->resultService->getDimensionScores($campaign);

        return view('engagement.admin.campaign_results', compact('campaign', 'results', 'nps', 'dimensions'));
    }

    public function adminActionPlans(Request $request): View
    {
        $plans = EngagementActionPlan::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->with(['owner', 'items'])
            ->get();

        return view('engagement.admin.action_plans', compact('plans'));
    }

    public function adminCulture(Request $request): View
    {
        $initiatives = CultureInitiative::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->with(['owner', 'actions'])
            ->get();

        return view('engagement.admin.culture', compact('initiatives'));
    }

    public function adminExecutive(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        $metrics = $this->analyticsService->generateEngagementMetrics($tenantId);
        $campaigns = EngagementCampaign::query()->where('tenant_id', $tenantId)->where('status', 'active')->get();

        return view('engagement.admin.executive', compact('metrics', 'campaigns'));
    }
}
