<?php

declare(strict_types=1);

namespace App\Domains\Billing\Http\Controllers;

use App\Domains\Billing\Support\CommercialFacade as Billing;
use App\Domains\Shared\Models\Tenant;
use Flow\Packages\Billing\Domain\Models\BillingInvoice;
use Flow\Packages\Billing\Domain\Models\BillingPlan;
use Flow\Packages\Billing\Domain\Models\BillingProduct;
use Flow\Packages\Billing\Domain\Models\BillingSubscription;
use Flow\Packages\Billing\Services\CreditDiscountService;
use Flow\Packages\Billing\Services\EntitlementResolver;
use Flow\Packages\Billing\Services\InvoiceEngine;
use Flow\Packages\Billing\Services\PaymentManager;
use Flow\Packages\Billing\Services\PricingEngine;
use Flow\Packages\Billing\Services\ProrationCalculator;
use Flow\Packages\Billing\Services\SubscriptionLifecycleService;
use Flow\Packages\Billing\Services\UsageMeteringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class BillingApiController extends Controller
{
    public function __construct(
        protected EntitlementResolver $entitlementResolver,
        protected SubscriptionLifecycleService $lifecycleService,
        protected UsageMeteringService $usageMeteringService,
        protected InvoiceEngine $invoiceEngine,
        protected PaymentManager $paymentManager,
        protected CreditDiscountService $creditDiscountService,
        protected PricingEngine $pricingEngine,
        protected ProrationCalculator $prorationCalculator
    ) {}

    protected function resolveTenantId(Request $request): string
    {
        return (string) (
            $request->header('X-Tenant-ID')
            ?? $request->query('tenant_id')
            ?? $request->user()?->tenant_id
            ?? session('tenant_uuid')
            ?? ''
        );
    }

    public function products(): JsonResponse
    {
        $products = BillingProduct::with(['versions', 'plans.prices', 'plans.entitlements'])
            ->where('is_active', true)
            ->get();

        return response()->json(['success' => true, 'data' => $products]);
    }

    public function plans(): JsonResponse
    {
        $plans = BillingPlan::with(['prices', 'entitlements', 'product'])
            ->where('status', 'active')
            ->get();

        return response()->json(['success' => true, 'data' => $plans]);
    }

    public function currentSubscription(Request $request): JsonResponse
    {
        $tenantId = $this->resolveTenantId($request);
        if ($tenantId === '') {
            return response()->json(['success' => false, 'error' => ['message' => 'Tenant context missing']], 400);
        }

        $subscription = BillingSubscription::with(['plan.prices', 'plan.entitlements', 'items'])
            ->where('tenant_id', $tenantId)
            ->latest('starts_at')
            ->first();

        $entitlements = $this->entitlementResolver->getAllEntitlements($tenantId);
        $creditBalance = $this->creditDiscountService->getAvailableBalance($tenantId);

        return response()->json([
            'success' => true,
            'data' => [
                'subscription' => $subscription,
                'entitlements' => $entitlements,
                'credit_balance' => $creditBalance,
            ],
        ]);
    }

    public function previewChange(Request $request): JsonResponse
    {
        $tenantId = $this->resolveTenantId($request);
        $request->validate([
            'plan_id' => 'required|string',
            'quantity' => 'nullable|integer|min:1',
        ]);

        $sub = $this->entitlementResolver->getActiveSubscription($tenantId);
        if (! $sub) {
            return response()->json(['success' => false, 'error' => ['message' => 'No active subscription found']], 404);
        }

        $newPlan = BillingPlan::with('prices')->findOrFail($request->input('plan_id'));
        $newQuantity = (int) ($request->input('quantity') ?? $sub->quantity);

        $currentCalc = $this->pricingEngine->calculatePlanTotal($sub->plan, ['seat' => $sub->quantity]);
        $newCalc = $this->pricingEngine->calculatePlanTotal($newPlan, ['seat' => $newQuantity]);

        $proration = $this->prorationCalculator->calculate(
            $currentCalc['subtotal'],
            $newCalc['subtotal'],
            $sub->current_cycle_start ?? now()->startOfMonth(),
            $sub->current_cycle_end ?? now()->endOfMonth(),
            now()
        );

        return response()->json([
            'success' => true,
            'data' => [
                'current_plan' => $sub->plan->name,
                'new_plan' => $newPlan->name,
                'current_recurring' => $currentCalc['subtotal'],
                'new_recurring' => $newCalc['subtotal'],
                'proration' => $proration,
            ],
        ]);
    }

    public function changeSubscription(Request $request): JsonResponse
    {
        $tenantId = $this->resolveTenantId($request);
        $request->validate([
            'plan_id' => 'required|string',
            'quantity' => 'nullable|integer|min:1',
        ]);

        $sub = $this->entitlementResolver->getActiveSubscription($tenantId);
        if (! $sub) {
            return response()->json(['success' => false, 'error' => ['message' => 'No active subscription found']], 404);
        }

        $newPlan = BillingPlan::with('prices')->findOrFail($request->input('plan_id'));
        $newQuantity = (int) ($request->input('quantity') ?? $sub->quantity);

        $result = $this->lifecycleService->changePlan($sub, $newPlan, $newQuantity);

        // If immediate charge required, generate invoice
        $invoice = null;
        if ($result['immediate_charge'] > 0) {
            $invoice = $this->invoiceEngine->generateSubscriptionInvoice($sub);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'message' => 'Subscription updated successfully',
                'subscription' => $result['subscription'],
                'proration' => $result['proration'],
                'invoice' => $invoice,
            ],
        ]);
    }

    public function invoices(Request $request): JsonResponse
    {
        $tenantId = $this->resolveTenantId($request);
        $invoices = BillingInvoice::with('items')
            ->where('tenant_id', $tenantId)
            ->latest('issue_date')
            ->paginate(15);

        return response()->json(['success' => true, 'data' => $invoices]);
    }

    public function invoiceDetail(string $id, Request $request): JsonResponse
    {
        $tenantId = $this->resolveTenantId($request);
        $invoice = BillingInvoice::with(['items', 'payments', 'subscription.plan'])
            ->where('tenant_id', $tenantId)
            ->findOrFail($id);

        return response()->json(['success' => true, 'data' => $invoice]);
    }

    public function payInvoice(string $id, Request $request): JsonResponse
    {
        $tenantId = $this->resolveTenantId($request);
        $invoice = BillingInvoice::where('tenant_id', $tenantId)->findOrFail($id);

        $request->validate([
            'amount' => 'nullable|numeric|min:0.01',
            'provider' => 'nullable|string',
        ]);

        $amount = (float) ($request->input('amount') ?? $invoice->balance_due);
        $provider = $request->input('provider') ?? 'mock';

        $payment = $this->paymentManager->processPayment(
            $invoice,
            $amount,
            $provider,
            $request->input('payment_method') ?? []
        );

        return response()->json([
            'success' => $payment->status->isSuccess(),
            'data' => [
                'payment' => $payment,
                'invoice' => $invoice->fresh(),
            ],
        ]);
    }

    public function applyDiscount(string $id, Request $request): JsonResponse
    {
        $tenantId = $this->resolveTenantId($request);
        $invoice = BillingInvoice::where('tenant_id', $tenantId)->findOrFail($id);

        $request->validate(['code' => 'required|string']);

        $res = $this->creditDiscountService->applyDiscount($invoice, $request->input('code'));

        return response()->json([
            'success' => $res['success'],
            'data' => $res,
            'invoice' => $invoice->fresh(),
        ]);
    }

    public function recordUsage(Request $request): JsonResponse
    {
        $tenantId = $this->resolveTenantId($request);
        $request->validate([
            'meter_key' => 'required|string',
            'quantity' => 'required|numeric',
            'idempotency_key' => 'required|string',
        ]);

        $recorded = $this->usageMeteringService->recordUsage(
            $tenantId,
            $request->input('meter_key'),
            (float) $request->input('quantity'),
            $request->input('idempotency_key'),
            $request->input('source') ?? 'api',
            $request->input('metadata') ?? []
        );

        return response()->json([
            'success' => true,
            'data' => [
                'recorded' => $recorded,
                'idempotency_status' => $recorded ? 'created' : 'ignored_duplicate',
            ],
        ]);
    }

    public function usageSummary(Request $request): JsonResponse
    {
        $tenantId = $this->resolveTenantId($request);
        $meters = ['active_employees', 'user_seats', 'api_calls', 'ai_tokens', 'storage_mb'];

        $summary = [];
        foreach ($meters as $m) {
            $summary[$m] = $this->usageMeteringService->evaluateThreshold($tenantId, $m);
        }

        return response()->json(['success' => true, 'data' => $summary]);
    }
}
