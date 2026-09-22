<?php

declare(strict_types=1);

namespace Tests\Security;

use App\Domains\Shared\Models\Tenant;
use Flow\Packages\Billing\Domain\Models\BillingInvoice;
use Flow\Packages\Billing\Domain\Models\BillingPlan;
use Flow\Packages\Billing\Domain\Models\BillingSubscription;
use Flow\Packages\Billing\Services\InvoiceEngine;
use Flow\Packages\Billing\Services\ProductPlanService;
use Flow\Packages\Billing\Services\SubscriptionLifecycleService;
use Flow\Packages\Billing\Services\UsageMeteringService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class BillingTenantIsolationTest extends TestCase
{
    use DatabaseTransactions;

    protected Tenant $tenantA;
    protected Tenant $tenantB;
    protected BillingPlan $plan;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'smarthcm',
        ]);

        app(ProductPlanService::class)->seedDefaultCatalog();
        $this->plan = BillingPlan::where('code', 'hcm-starter')->firstOrFail();

        $this->tenantA = Tenant::firstOrCreate(
            ['tenant_code' => 'ISO-ALPHA-01'],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Tenant Alpha Corp',
                'slug' => 'tenant-isolation-alpha-' . Str::random(6),
                'status' => 'active',
                'currency' => 'USD',
                'is_active' => true,
            ]
        );

        $this->tenantB = Tenant::firstOrCreate(
            ['tenant_code' => 'ISO-BETA-01'],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Tenant Beta Corp',
                'slug' => 'tenant-isolation-beta-' . Str::random(6),
                'status' => 'active',
                'currency' => 'USD',
                'is_active' => true,
            ]
        );
    }

    public function test_tenant_a_cannot_view_tenant_b_invoices(): void
    {
        $lifecycle = app(SubscriptionLifecycleService::class);
        $invoiceEngine = app(InvoiceEngine::class);

        // Provision subscription & invoice for Tenant B
        $subB = $lifecycle->activateSubscription($this->tenantB, $this->plan, 1);
        $invoiceB = $invoiceEngine->generateSubscriptionInvoice($subB);

        // Tenant A queries invoices list
        $responseA = $this->withHeader('X-Tenant-ID', $this->tenantA->id)
            ->getJson('/api/v1/billing/invoices');

        $responseA->assertStatus(200);
        $invoiceIds = collect($responseA->json('data.data'))->pluck('id')->all();
        $this->assertNotContains($invoiceB->id, $invoiceIds);

        // Tenant A tries to directly view Tenant B's invoice by ID -> returns 404 (Resource Not Found due to tenant scoping)
        $detailRes = $this->withHeader('X-Tenant-ID', $this->tenantA->id)
            ->getJson('/api/v1/billing/invoices/' . $invoiceB->id);

        $this->assertTrue(in_array($detailRes->status(), [403, 404]));
    }

    public function test_tenant_a_cannot_view_tenant_b_subscription(): void
    {
        $lifecycle = app(SubscriptionLifecycleService::class);
        $subB = $lifecycle->activateSubscription($this->tenantB, $this->plan, 5);

        // Tenant A queries current subscription
        $resA = $this->withHeader('X-Tenant-ID', $this->tenantA->id)
            ->getJson('/api/v1/billing/subscriptions/current');

        $resA->assertStatus(200);
        $subId = $resA->json('data.subscription.id');
        $this->assertNotSame($subB->id, $subId);
    }

    public function test_webhook_hmac_signature_verification_rejects_tampered_payload(): void
    {
        $payload = json_encode(['id' => 'evt_test_123', 'type' => 'charge.succeeded']);
        $invalidSignature = 'sha256_fake_signature_' . Str::random(32);

        $response = $this->withHeaders([
            'Stripe-Signature' => $invalidSignature,
            'Content-Type' => 'application/json',
        ])->postJson('/api/v1/billing/webhooks/stripe', json_decode($payload, true));

        $response->assertStatus(400)
            ->assertJson(['error' => 'Invalid webhook signature']);
    }

    public function test_usage_metering_strict_idempotency(): void
    {
        $meterService = app(UsageMeteringService::class);
        $idempotencyKey = 'idemp_key_' . Str::random(16);

        // First event succeeds
        $first = $meterService->recordUsage($this->tenantA->id, 'api_calls', 10.0, $idempotencyKey, 'test');
        $this->assertTrue($first);

        // Second identical event is rejected/ignored
        $second = $meterService->recordUsage($this->tenantA->id, 'api_calls', 10.0, $idempotencyKey, 'test');
        $this->assertFalse($second);
    }
}
