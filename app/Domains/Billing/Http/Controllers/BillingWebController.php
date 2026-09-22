<?php

declare(strict_types=1);

namespace App\Domains\Billing\Http\Controllers;

use App\Domains\Shared\Models\Tenant;
use Flow\Packages\Billing\Domain\Models\BillingInvoice;
use Flow\Packages\Billing\Domain\Models\BillingPayment;
use Flow\Packages\Billing\Domain\Models\BillingPlan;
use Flow\Packages\Billing\Domain\Models\BillingProduct;
use Flow\Packages\Billing\Domain\Models\BillingReconciliationRecord;
use Flow\Packages\Billing\Domain\Models\BillingSubscription;
use Flow\Packages\Billing\Services\CommercialAnalyticsService;
use Flow\Packages\Billing\Services\CreditDiscountService;
use Flow\Packages\Billing\Services\EntitlementResolver;
use Flow\Packages\Billing\Services\InvoiceEngine;
use Flow\Packages\Billing\Services\PaymentManager;
use Flow\Packages\Billing\Services\PricingEngine;
use Flow\Packages\Billing\Services\ProductPlanService;
use Flow\Packages\Billing\Services\SubscriptionLifecycleService;
use Flow\Packages\Billing\Services\UsageMeteringService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class BillingWebController extends Controller
{
    public function __construct(
        protected CommercialAnalyticsService $analyticsService,
        protected EntitlementResolver $entitlementResolver,
        protected SubscriptionLifecycleService $lifecycleService,
        protected InvoiceEngine $invoiceEngine,
        protected PaymentManager $paymentManager,
        protected CreditDiscountService $creditDiscountService,
        protected UsageMeteringService $usageMeteringService,
        protected ProductPlanService $productPlanService,
        protected PricingEngine $pricingEngine
    ) {}

    protected function resolveTenant(Request $request): ?Tenant
    {
        $tenantId = (string) (
            $request->header('X-Tenant-ID')
            ?? $request->query('tenant_id')
            ?? $request->user()?->tenant_id
            ?? session('tenant_uuid')
            ?? ''
        );

        if ($tenantId !== '') {
            return Tenant::find($tenantId);
        }

        // Fallback for demo/local environment
        return Tenant::first();
    }

    /**
     * Admin Command Center: /operations/billing
     */
    public function adminOverview(): View
    {
        $this->productPlanService->seedDefaultCatalog();

        $metrics = $this->analyticsService->getExecutiveMetrics();
        $recentSubscriptions = BillingSubscription::with(['tenant', 'plan'])->latest()->limit(8)->get();
        $recentInvoices = BillingInvoice::with('tenant')->latest()->limit(8)->get();
        $recentDiscrepancies = BillingReconciliationRecord::where('status', 'discrepancy')->latest()->limit(5)->get();
        $plans = BillingPlan::with(['prices', 'entitlements'])->where('status', 'active')->get();

        return view('operations.billing.index', compact(
            'metrics',
            'recentSubscriptions',
            'recentInvoices',
            'recentDiscrepancies',
            'plans'
        ));
    }

    /**
     * Tenant Self-Service Portal: /portal/billing
     */
    public function tenantPortal(Request $request): View
    {
        $this->productPlanService->seedDefaultCatalog();
        $tenant = $this->resolveTenant($request);

        if (! $tenant) {
            abort(404, 'No tenant found for billing portal.');
        }

        $subscription = BillingSubscription::with(['plan.prices', 'plan.entitlements'])
            ->where('tenant_id', $tenant->id)
            ->latest('starts_at')
            ->first();

        // If tenant has no subscription, auto-provision a starter trial
        if (! $subscription) {
            $starterPlan = BillingPlan::where('code', 'hcm-starter')->first();
            if ($starterPlan) {
                $subscription = $this->lifecycleService->startTrial($tenant, $starterPlan, 14);
            }
        }

        $entitlements = $this->entitlementResolver->getAllEntitlements($tenant->id);
        $creditBalance = $this->creditDiscountService->getAvailableBalance($tenant->id);
        $invoices = BillingInvoice::with('items')
            ->where('tenant_id', $tenant->id)
            ->latest('issue_date')
            ->limit(10)
            ->get();

        $availablePlans = BillingPlan::with(['prices', 'entitlements'])->where('status', 'active')->get();

        $meters = [
            'active_employees' => $this->usageMeteringService->evaluateThreshold($tenant->id, 'active_employees', 'employee_limit'),
            'user_seats' => $this->usageMeteringService->evaluateThreshold($tenant->id, 'user_seats', 'user_limit'),
            'api_calls' => $this->usageMeteringService->evaluateThreshold($tenant->id, 'api_calls'),
            'ai_tokens' => $this->usageMeteringService->evaluateThreshold($tenant->id, 'ai_tokens'),
        ];

        return view('portal.billing.index', compact(
            'tenant',
            'subscription',
            'entitlements',
            'creditBalance',
            'invoices',
            'availablePlans',
            'meters'
        ));
    }

    /**
     * Tenant upgrade/change plan form action.
     */
    public function tenantChangePlan(Request $request): RedirectResponse
    {
        $tenant = $this->resolveTenant($request);
        $request->validate(['plan_id' => 'required|string']);

        $sub = $this->entitlementResolver->getActiveSubscription($tenant->id);
        $newPlan = BillingPlan::findOrFail($request->input('plan_id'));

        if ($sub) {
            $result = $this->lifecycleService->changePlan($sub, $newPlan, (int) ($request->input('quantity', 1)));
            if ($result['immediate_charge'] > 0) {
                $this->invoiceEngine->generateSubscriptionInvoice($sub);
            }
        } else {
            $this->lifecycleService->activateSubscription($tenant, $newPlan, 1);
        }

        return redirect('/portal/billing')->with('success', "Your subscription has been updated to {$newPlan->name}.");
    }

    /**
     * Tenant Pay Invoice action.
     */
    public function tenantPayInvoice(string $id, Request $request): RedirectResponse
    {
        $tenant = $this->resolveTenant($request);
        $invoice = BillingInvoice::where('tenant_id', $tenant->id)->findOrFail($id);

        $payment = $this->paymentManager->processPayment(
            $invoice,
            (float) $invoice->balance_due,
            'mock',
            ['type' => 'card', 'token' => 'tok_visa']
        );

        if ($payment->status->isSuccess()) {
            return redirect('/portal/billing')->with('success', "Invoice {$invoice->invoice_number} paid successfully.");
        }

        return redirect('/portal/billing')->with('error', "Payment failed: {$payment->failure_reason}");
    }

    /**
     * View/Print Invoice Receipt.
     */
    public function tenantDownloadInvoice(string $id, Request $request): View
    {
        $tenant = $this->resolveTenant($request);
        $invoice = BillingInvoice::with(['items', 'payments', 'subscription.plan'])
            ->where('tenant_id', $tenant->id)
            ->findOrFail($id);

        return view('portal.billing.invoice_receipt', compact('tenant', 'invoice'));
    }
}
