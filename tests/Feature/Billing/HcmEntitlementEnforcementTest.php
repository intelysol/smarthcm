<?php

declare(strict_types=1);

namespace Tests\Feature\Billing;

use App\Domains\Billing\Support\CommercialFacade as Billing;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use Flow\Packages\Billing\Domain\Models\BillingPlan;
use Flow\Packages\Billing\Services\ProductPlanService;
use Flow\Packages\Billing\Services\SubscriptionLifecycleService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Tests\TestCase;

class HcmEntitlementEnforcementTest extends TestCase
{
    use DatabaseTransactions;

    protected Tenant $tenant;
    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'smarthcm',
        ]);

        app(ProductPlanService::class)->seedDefaultCatalog();

        $this->tenant = Tenant::firstOrCreate(
            ['slug' => 'hcm-entitlement-corp'],
            [
                'id' => (string) Str::uuid(),
                'name' => 'HCM Entitlement Corp',
                'tenant_code' => 'ENT-001',
                'status' => 'active',
                'currency' => 'USD',
                'is_active' => true,
            ]
        );

        $this->company = Company::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'name' => 'ENT Global HQ'],
            ['currency' => 'USD', 'timezone' => 'UTC']
        );

        // Define test routes protected by commercial.entitlement
        Route::get('/test/hcm/payroll', function () {
            return response()->json(['success' => true, 'module' => 'payroll']);
        })->middleware(['commercial.entitlement:payroll_enabled']);

        Route::get('/test/hcm/recruitment', function () {
            return response()->json(['success' => true, 'module' => 'recruitment']);
        })->middleware(['commercial.entitlement:recruitment_enabled']);
    }

    public function test_starter_plan_blocks_payroll_module_entitlement(): void
    {
        $starter = BillingPlan::where('code', 'hcm-starter')->firstOrFail();
        app(SubscriptionLifecycleService::class)->activateSubscription($this->tenant, $starter, 1);

        // Starter has payroll_enabled = false -> HTTP 403 Forbidden with COMMERCIAL_ENTITLEMENT_REQUIRED
        $res = $this->withHeader('X-Tenant-ID', $this->tenant->id)
            ->getJson('/test/hcm/payroll');

        $res->assertStatus(403)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error.code', 'COMMERCIAL_ENTITLEMENT_REQUIRED');
    }

    public function test_professional_plan_unlocks_payroll_module_entitlement(): void
    {
        $pro = BillingPlan::where('code', 'hcm-professional')->firstOrFail();
        app(SubscriptionLifecycleService::class)->activateSubscription($this->tenant, $pro, 1);

        // Professional has payroll_enabled = true -> HTTP 200 OK
        $res = $this->withHeader('X-Tenant-ID', $this->tenant->id)
            ->getJson('/test/hcm/payroll');

        $res->assertStatus(200)->assertJsonPath('success', true);
    }

    public function test_employee_seat_limit_enforcement(): void
    {
        $starter = BillingPlan::where('code', 'hcm-starter')->firstOrFail();
        // Starter limit is 10 employees
        app(SubscriptionLifecycleService::class)->activateSubscription($this->tenant, $starter, 1);

        $this->assertSame(10, Billing::getLimit($this->tenant->id, 'employee_limit'));

        // Pre-create 10 active employees
        for ($i = 1; $i <= 10; $i++) {
            Employee::firstOrCreate(
                ['tenant_id' => $this->tenant->id, 'official_email' => "emp{$i}@ent-test.internal"],
                [
                    'company_id' => $this->company->id,
                    'employee_number' => 'EMP-ENT-' . (1000 + $i),
                    'first_name' => 'Emp',
                    'last_name' => (string) $i,
                    'employment_status' => 'active',
                    'joining_date' => now()->toDateString(),
                ]
            );
        }

        // Now tenant is at limit -> cannot add more employees
        $canAdd = Billing::canAddEmployee($this->tenant->id);
        $this->assertFalse($canAdd);

        // Upgrade to Enterprise (limit 200)
        $enterprise = BillingPlan::where('code', 'hcm-enterprise')->firstOrFail();
        app(SubscriptionLifecycleService::class)->activateSubscription($this->tenant, $enterprise, 1);

        // Now can add employee
        $this->assertTrue(Billing::canAddEmployee($this->tenant->id));
    }
}
