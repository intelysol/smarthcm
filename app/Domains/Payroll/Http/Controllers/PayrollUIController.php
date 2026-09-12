<?php

namespace App\Domains\Payroll\Http\Controllers;

use App\Domains\Employee\Models\Employee;
use App\Domains\Payroll\Models\CompensationComponent;
use App\Domains\Payroll\Models\CompensationStructure;
use App\Domains\Payroll\Models\PayrollAdjustment;
use App\Domains\Payroll\Models\PayrollCalendar;
use App\Domains\Payroll\Models\PayrollLegalEntity;
use App\Domains\Payroll\Models\PayrollPaymentBatch;
use App\Domains\Payroll\Models\PayrollPayslip;
use App\Domains\Payroll\Models\PayrollPeriod;
use App\Domains\Payroll\Models\PayrollPolicy;
use App\Domains\Payroll\Models\PayrollRun;
use App\Domains\Payroll\Models\PayrollTaxRule;
use App\Domains\Payroll\Services\PayrollAnalyticsService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PayrollUIController extends Controller
{
    public function __construct(protected PayrollAnalyticsService $analyticsService) {}

    public function dashboard(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        $periods = PayrollPeriod::query()->where('tenant_id', $tenantId)->orderByDesc('start_date')->take(5)->get();
        $runs = PayrollRun::query()->where('tenant_id', $tenantId)->orderByDesc('created_at')->take(5)->get();
        $adjustments = PayrollAdjustment::query()->where('tenant_id', $tenantId)->where('status', 'pending')->count();
        $batches = PayrollPaymentBatch::query()->where('tenant_id', $tenantId)->where('status', 'draft')->count();

        return view('payroll.dashboard', compact('periods', 'runs', 'adjustments', 'batches'));
    }

    public function periods(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        $periods = PayrollPeriod::query()
            ->where('tenant_id', $tenantId)
            ->with(['calendar', 'legalEntity'])
            ->orderByDesc('start_date')
            ->paginate(15);

        return view('payroll.periods.index', compact('periods'));
    }

    public function runs(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        $runs = PayrollRun::query()
            ->where('tenant_id', $tenantId)
            ->with(['period', 'legalEntity'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('payroll.runs.index', compact('runs'));
    }

    public function runDetails(PayrollRun $run): View
    {
        $run->load(['period', 'calculationSnapshots.employee', 'exceptions', 'variances.employee']);
        $kpis = $this->analyticsService->getRunSummary($run);

        return view('payroll.runs.show', compact('run', 'kpis'));
    }

    public function structures(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        $structures = CompensationStructure::query()
            ->where('tenant_id', $tenantId)
            ->with(['structureComponents.component'])
            ->get();

        $components = CompensationComponent::query()->where('tenant_id', $tenantId)->get();

        return view('payroll.structures.index', compact('structures', 'components'));
    }

    public function adjustments(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        $adjustments = PayrollAdjustment::query()
            ->where('tenant_id', $tenantId)
            ->with(['employee', 'period', 'requester'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('payroll.adjustments.index', compact('adjustments'));
    }

    public function payslips(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        $payslips = PayrollPayslip::query()
            ->where('tenant_id', $tenantId)
            ->with(['employee', 'run.period'])
            ->orderByDesc('period_start')
            ->paginate(20);

        return view('payroll.payslips.index', compact('payslips'));
    }

    public function payslipShow(PayrollPayslip $payslip): View
    {
        $payslip->load(['employee.department', 'run.period', 'snapshot.lines']);
        return view('payroll.payslips.show', compact('payslip'));
    }

    public function paymentBatches(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        $batches = PayrollPaymentBatch::query()
            ->where('tenant_id', $tenantId)
            ->with(['run.period'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('payroll.payments.index', compact('batches'));
    }

    public function reports(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        $latestRun = PayrollRun::query()->where('tenant_id', $tenantId)->orderByDesc('calculated_at')->first();
        $kpis = $latestRun ? $this->analyticsService->getRunSummary($latestRun) : null;

        return view('payroll.reports.index', compact('latestRun', 'kpis'));
    }

    public function settings(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        $taxRules = PayrollTaxRule::query()->where('tenant_id', $tenantId)->with('versions')->get();
        $policies = PayrollPolicy::query()->where('tenant_id', $tenantId)->get();
        $legalEntities = PayrollLegalEntity::query()->where('tenant_id', $tenantId)->get();
        $calendars = PayrollCalendar::query()->where('tenant_id', $tenantId)->get();

        return view('payroll.settings.index', compact('taxRules', 'policies', 'legalEntities', 'calendars'));
    }
}
