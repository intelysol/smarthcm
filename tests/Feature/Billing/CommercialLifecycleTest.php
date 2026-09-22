<?php

declare(strict_types=1);

namespace Tests\Feature\Billing;

use App\Domains\Shared\Models\Tenant;
use Flow\Packages\Billing\Domain\Enums\InvoiceStatus;
use Flow\Packages\Billing\Domain\Enums\PaymentStatus;
use Flow\Packages\Billing\Domain\Enums\SubscriptionStatus;
use Flow\Packages\Billing\Domain\Models\BillingDiscount;
use Flow\Packages\Billing\Domain\Models\BillingPlan;
use Flow\Packages\Billing\Domain\Models\BillingSubscription;
use Flow\Packages\Billing\Services\BillingReconciliationService;
use Flow\Packages\Billing\Services\CreditDiscountService;
use Flow\Packages\Billing\Services\EntitlementResolver;
use Flow\Packages\Billing\Services\InvoiceEngine;
use Flow\Packages\Billing\Services\PaymentManager;
use Flow\Packages\Billing\Services\ProductPlanService;
use Flow\Packages\Billing\Services\SubscriptionLifecycleService;
use Flow\Packages\Billing\Services\UsageMeteringService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class CommercialLifecycleTest extends TestCase
{
    use DatabaseTransactions;

    protected Tenant $tenant;
    protected ProductPlanService $planService;
    protected SubscriptionLifecycleService $lifecycleService;
    protected EntitlementResolver $entitlementResolver;
    protected UsageMeteringService $meteringService;
    protected InvoiceEngine $invoiceEngine;
    protected PaymentManager $paymentManager;
    protected CreditDiscountService $creditDiscountService;
    protected BillingReconciliationService $reconciliationService;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'smarthcm',
        ]);

        $this->planService = app(ProductPlanService::class);
        $this->lifecycleService = app(SubscriptionLifecycleService::class);
        $this->entitlementResolver = app(EntitlementResolver::class);
        $this->meteringService = app(UsageMeteringService::class);
        $this->invoiceEngine = app(InvoiceEngine::class);
        $this->paymentManager = app(PaymentManager::class);
        $this->creditDiscountService = app(CreditDiscountService::class);
        $this->reconciliationService = app(BillingReconciliationService::class);

        $this->planService->seedDefaultCatalog();

        $uniqueSuffix = Str::random(8);
        $this->tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Commercial Test Corp ' . $uniqueSuffix,
            'slug' => 'commercial-test-' . $uniqueSuffix,
            'tenant_code' => 'COM-' . strtoupper($uniqueSuffix),
            'status' => 'active',
            'currency' => 'USD',
            'country_code' => 'US',
            'is_active' => true,
        ]);
    }

    public function test_commercial_full_lifecycle_journey(): void
    {
        $starterPlan = BillingPlan::where('code', 'hcm-starter')->firstOrFail();
        $proPlan = BillingPlan::where('code', 'hcm-professional')->firstOrFail();

        // 1. Start Commercial Trial
        $trialSub = $this->lifecycleService->startTrial($this->tenant, $starterPlan, 14);
        $this->assertSame(SubscriptionStatus::TRIALING, $trialSub->status);
        $this->assertTrue($trialSub->isTrialing());
        $this->assertTrue($trialSub->isUsable());

        // 2. Entitlements resolved on trial
        $this->assertTrue($this->entitlementResolver->hasFeature($this->tenant->id, 'core_hr_enabled'));
        $this->assertTrue($this->entitlementResolver->hasFeature($this->tenant->id, 'attendance_enabled'));
        $this->assertFalse($this->entitlementResolver->hasFeature($this->tenant->id, 'payroll_enabled'));
        $this->assertSame(10, $this->entitlementResolver->getLimit($this->tenant->id, 'employee_limit'));

        // 3. Record Metered Usage (Idempotent)
        $key = 'evt_test_' . Str::random(12);
        $recorded = $this->meteringService->recordUsage($this->tenant->id, 'api_calls', 25.0, $key, 'test');
        $this->assertTrue($recorded);

        // Duplicate event with same key ignored
        $duplicate = $this->meteringService->recordUsage($this->tenant->id, 'api_calls', 25.0, $key, 'test');
        $this->assertFalse($duplicate);

        // 4. Upgrade to Professional Plan with Proration
        $changeResult = $this->lifecycleService->changePlan($trialSub, $proPlan, 25);
        $this->assertSame($proPlan->id, $trialSub->fresh()->plan_id);
        $this->assertArrayHasKey('proration', $changeResult);

        // 5. Entitlement now unlocks Payroll
        $this->assertTrue($this->entitlementResolver->hasFeature($this->tenant->id, 'payroll_enabled'));
        $this->assertSame(50, $this->entitlementResolver->getLimit($this->tenant->id, 'employee_limit'));

        // 6. Generate Commercial Invoice
        $invoice = $this->invoiceEngine->generateSubscriptionInvoice($trialSub->fresh());
        $this->assertNotNull($invoice->id);
        $this->assertSame(InvoiceStatus::ISSUED, $invoice->status);
        $this->assertGreaterThan(0, $invoice->total_amount);
        $this->assertGreaterThan(0, $invoice->items()->count());

        // 7. Apply Promotional Coupon
        $discount = BillingDiscount::create([
            'id' => (string) Str::uuid(),
            'code' => 'SAVE20-' . Str::random(4),
            'name' => '20% Welcome Discount',
            'discount_type' => 'percentage',
            'value' => 20.00,
            'is_active' => true,
        ]);

        $discRes = $this->creditDiscountService->applyDiscount($invoice, $discount->code);
        $this->assertTrue($discRes['success']);
        $this->assertGreaterThan(0, $invoice->fresh()->discount_amount);

        // 8. Settle Invoice via Payment Manager
        $payment = $this->paymentManager->processPayment(
            $invoice->fresh(),
            (float) $invoice->fresh()->balance_due,
            'mock',
            ['type' => 'card', 'token' => 'tok_visa']
        );

        $this->assertSame(PaymentStatus::SUCCEEDED, $payment->status);
        $this->assertTrue($invoice->fresh()->isPaid());
        $this->assertSame(0.00, (float) $invoice->fresh()->balance_due);

        // 9. Process Partial Refund
        $refund = $this->paymentManager->processRefund($payment, 10.00, 'Customer satisfaction discount');
        $this->assertSame('completed', $refund->status);
        $this->assertSame(PaymentStatus::PARTIALLY_REFUNDED, $payment->fresh()->status);
        $this->assertSame(10.00, (float) $invoice->fresh()->balance_due);

        // 10. Issue and Apply Credit Wallet
        $credit = $this->creditDiscountService->issueCredit($this->tenant->id, 50.00, 'Service compensation');
        $this->assertSame(50.00, (float) $credit->balance);

        $applied = $this->invoiceEngine->applyAvailableCredits($invoice->fresh());
        $this->assertSame(10.00, $applied);
        $this->assertTrue($invoice->fresh()->isPaid());

        // 11. Subscription Suspension & Reactivation
        $this->lifecycleService->suspend($trialSub->fresh(), 'Administrative review');
        $this->assertSame(SubscriptionStatus::SUSPENDED, $trialSub->fresh()->status);
        $this->assertFalse($trialSub->fresh()->isUsable());
        $this->assertFalse($this->entitlementResolver->hasFeature($this->tenant->id, 'core_hr_enabled'));

        $this->lifecycleService->reactivate($trialSub->fresh());
        $this->assertSame(SubscriptionStatus::ACTIVE, $trialSub->fresh()->status);
        $this->assertTrue($trialSub->fresh()->isUsable());

        // 12. Commercial Reconciliation Audit
        $audit = $this->reconciliationService->auditDaily();
        $this->assertArrayHasKey('total_audited', $audit);
        $this->assertArrayHasKey('discrepancies_found', $audit);
    }
}
