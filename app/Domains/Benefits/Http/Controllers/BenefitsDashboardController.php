<?php

namespace App\Domains\Benefits\Http\Controllers;

use App\Domains\Benefits\Models\BenefitEnrollment;
use App\Domains\Benefits\Models\BenefitPlan;
use App\Domains\Benefits\Models\InsuranceClaim;
use App\Domains\Benefits\Models\LoanApplication;
use App\Domains\Benefits\Models\RetirementAccount;
use App\Domains\Benefits\Services\BenefitsAnalyticsService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BenefitsDashboardController extends Controller
{
    public function __construct(
        protected BenefitsAnalyticsService $analyticsService
    ) {}

    public function index(Request $request): View
    {
        $tenantId = $request->user()?->tenant_id ?? 'default';
        $summary = $this->analyticsService->getExecutiveSummary($tenantId);

        $recentEnrollments = BenefitEnrollment::with(['employee', 'plan'])->latest()->take(5)->get();
        $recentLoans = LoanApplication::with(['employee', 'product'])->latest()->take(5)->get();
        $recentClaims = InsuranceClaim::with(['employee'])->latest()->take(5)->get();

        return view('benefits.dashboard', compact('summary', 'recentEnrollments', 'recentLoans', 'recentClaims'));
    }

    public function plans(): View
    {
        $plans = BenefitPlan::with(['category', 'provider'])->latest()->paginate(15);
        return view('benefits.plans.index', compact('plans'));
    }

    public function enrollments(): View
    {
        $enrollments = BenefitEnrollment::with(['employee', 'plan'])->latest()->paginate(15);
        return view('benefits.enrollments.index', compact('enrollments'));
    }

    public function claims(): View
    {
        $claims = InsuranceClaim::with(['employee'])->latest()->paginate(15);
        return view('benefits.claims.index', compact('claims'));
    }

    public function loans(): View
    {
        $loans = LoanApplication::with(['employee', 'product', 'activeSchedule'])->latest()->paginate(15);
        return view('benefits.loans.index', compact('loans'));
    }

    public function showLoan(LoanApplication $loan): View
    {
        $loan->load(['employee', 'product', 'agreement', 'activeSchedule.installments', 'transactions', 'restructures', 'settlements']);
        return view('benefits.loans.show', compact('loan'));
    }

    public function programs(): View
    {
        $programs = \App\Domains\Benefits\Models\BenefitProgram::with('plans')->latest()->paginate(15);
        return view('benefits.programs.index', compact('programs'));
    }

    public function openEnrollment(): View
    {
        $windows = \App\Domains\Benefits\Models\BenefitEnrollmentWindow::latest()->paginate(10);
        return view('benefits.open_enrollment.index', compact('windows'));
    }

    public function selfService(): View
    {
        $plans = BenefitPlan::with('coverages')->where('status', 'active')->get();
        return view('benefits.self_service.wizard', compact('plans'));
    }

    public function lifeEvents(): View
    {
        $events = \App\Domains\Benefits\Models\BenefitLifeEvent::with(['employee', 'lifeEventType'])->latest()->paginate(15);
        $types = \App\Domains\Benefits\Models\BenefitLifeEventType::where('is_active', true)->get();
        return view('benefits.life_events.index', compact('events', 'types'));
    }

    public function reconciliation(): View
    {
        $reconciliations = \App\Domains\Benefits\Models\BenefitReconciliation::with(['employee', 'enrollment.plan'])->latest()->paginate(20);
        return view('benefits.reconciliation.index', compact('reconciliations'));
    }
}
