<?php

namespace Tests\Feature\Expenses;

use App\Domains\Employee\Models\Employee;
use App\Domains\Expenses\Models\ExpenseCategory;
use App\Domains\Expenses\Models\ExpenseClaim;
use App\Domains\Expenses\Models\ExpensePolicy;
use App\Domains\Expenses\Models\PerDiemRate;
use App\Domains\Expenses\Models\TravelAdvance;
use App\Domains\Expenses\Services\ExpenseAiAdvisoryService;
use App\Domains\Expenses\Services\ExpenseClaimService;
use App\Domains\Expenses\Services\ExpensePolicyService;
use App\Domains\Expenses\Services\TravelAdvanceService;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseAiAdvisoryAndEnterpriseFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_ai_advisory_policy_explanation_claim_pre_validation_and_guardrails(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $employee = $this->createEmployee($tenant->id, $company->id);

        $mealCategory = ExpenseCategory::create([
            'tenant_id' => $tenant->id,
            'code' => 'MEALS',
            'name' => 'Meals & Dining',
            'category_type' => 'meals',
            'receipt_required' => true,
            'receipt_threshold' => 50.0000,
        ]);

        $hotelCategory = ExpenseCategory::create([
            'tenant_id' => $tenant->id,
            'code' => 'HOTEL',
            'name' => 'Hotel Lodging',
            'category_type' => 'lodging',
            'receipt_required' => true,
            'receipt_threshold' => 100.0000,
        ]);

        $policyService = app(ExpensePolicyService::class);
        $policy = $policyService->createPolicy([
            'tenant_id' => $tenant->id,
            'name' => 'Enterprise Standard Policy',
            'daily_meal_limit' => 150.0000,
            'daily_hotel_limit' => 300.0000,
            'receipt_required_threshold' => 50.0000,
        ]);
        $policyService->assignPolicy($policy, 'employee', $employee->id, 1);

        $aiService = app(ExpenseAiAdvisoryService::class);

        // 1. Test explainPolicyRules
        $policyExplanation = $aiService->explainPolicyRules($tenant->id, 'MEALS', $employee);
        $this->assertTrue($policyExplanation['is_advisory']);
        $this->assertStringContainsString('strictly advisory', $policyExplanation['disclaimer']);
        $this->assertEquals(150.0000, $policyExplanation['daily_meal_limit']);
        $this->assertNotEmpty($policyExplanation['categories']);
        $this->assertEquals('MEALS', $policyExplanation['categories'][0]['code']);

        // 2. Test preValidateClaim on draft claim
        $claimService = app(ExpenseClaimService::class);
        $claim = $claimService->createClaim($employee, [
            'title' => 'Chicago Business Trip',
            'claim_date' => '2026-10-15',
        ]);

        // When empty, preValidateClaim reports blockers
        $preCheckEmpty = $aiService->preValidateClaim($claim);
        $this->assertFalse($preCheckEmpty['can_submit']);
        $this->assertContains('Claim has no expense lines attached.', $preCheckEmpty['blockers']);

        // Add line exceeding meal cap and without receipts
        $line1 = $claimService->addLine($claim, [
            'expense_category_id' => $mealCategory->id,
            'expense_date' => '2026-10-15',
            'description' => 'Dinner with client executive team',
            'original_amount' => 200.0000, // Exceeds $150 cap
        ]);

        $preCheckFilled = $aiService->preValidateClaim($claim->fresh());
        $this->assertTrue($preCheckFilled['can_submit']);
        $this->assertEquals('warnings_present', $preCheckFilled['overall_status']);
        $this->assertNotEmpty($preCheckFilled['warnings']);

        // 3. Test estimatePerDiem
        PerDiemRate::create([
            'tenant_id' => $tenant->id,
            'destination_type' => 'international',
            'destination_country' => 'UK',
            'destination_city' => 'London',
            'daily_rate' => 120.0000,
            'currency' => 'GBP',
            'departure_day_percentage' => 75.00,
            'return_day_percentage' => 75.00,
            'breakfast_deduction_percentage' => 20.00,
            'lunch_deduction_percentage' => 30.00,
            'dinner_deduction_percentage' => 30.00,
            'effective_from' => '2026-01-01',
            'is_active' => true,
        ]);

        $perDiemEst = $aiService->estimatePerDiem(
            $tenant->id,
            'international',
            'UK',
            'London',
            3,
            true,
            true,
            [1 => ['breakfast'], 2 => ['lunch'], 3 => []]
        );

        $this->assertTrue($perDiemEst['is_advisory']);
        $this->assertEquals(3, $perDiemEst['days']);
        $this->assertEquals(120.0000, $perDiemEst['base_daily_rate']);
        $this->assertGreaterThan(0, $perDiemEst['total_estimated_per_diem']);
        $this->assertCount(3, $perDiemEst['days_breakdown']);

        // 4. Test recommendAdvanceSettlement
        $advanceService = app(TravelAdvanceService::class);
        $advance = $advanceService->requestAdvance($employee, ['requested_amount' => 500.0000]);
        $advanceService->approveAdvance($advance, 500.0000, User::factory()->create(['tenant_id' => $tenant->id]));
        $advanceService->disburseAdvance($advance, ['disbursed_amount' => 500.0000], User::factory()->create(['tenant_id' => $tenant->id]));

        $rec = $aiService->recommendAdvanceSettlement($employee, $claim->fresh());
        $this->assertTrue($rec['is_advisory']);
        $this->assertEquals(500.0000, $rec['total_outstanding_advances']);
        $this->assertNotEmpty($rec['recommended_steps']);

        // 5. Test strict guardrail refusing autonomous execution
        $blockedAction = $aiService->executeAutonomousAction('approve_claim');
        $this->assertTrue($blockedAction['action_blocked']);
        $this->assertFalse($blockedAction['success']);
        $this->assertStringContainsString('strictly advisory', $blockedAction['reason']);
    }

    public function test_web_routes_for_policies_cards_accounting_and_ai_advisor(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $this->actingAs($user);

        // 1. Policies Web View & JSON
        $respPolicies = $this->get(route('expenses.policies.index'));
        $respPolicies->assertOk();

        $respPoliciesJson = $this->getJson(route('expenses.policies.index'));
        $respPoliciesJson->assertOk()
            ->assertJsonStructure(['policies', 'categories']);

        // 2. Corporate Cards Web View & JSON
        $respCards = $this->get(route('expenses.cards.index'));
        $respCards->assertOk();

        $respCardsJson = $this->getJson(route('expenses.cards.index'));
        $respCardsJson->assertOk()
            ->assertJsonStructure(['cards', 'transactions', 'metrics']);

        // 3. Accounting Web View, Export & Lock
        $respAcct = $this->get(route('expenses.accounting.index'));
        $respAcct->assertOk();

        $respExport = $this->postJson(route('expenses.accounting.export'), [
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-30',
        ]);
        $respExport->assertStatus(201)
            ->assertJsonPath('message', 'Accounting export generated successfully.');

        $respLock = $this->postJson(route('expenses.accounting.lock'), [
            'period_name' => '2026-Q3 Lock',
            'start_date' => '2026-07-01',
            'end_date' => '2026-09-30',
        ]);
        $respLock->assertStatus(201)
            ->assertJsonPath('message', 'Accounting period locked successfully.');

        // 4. AI Advisor View & Query API
        $respAdvisor = $this->get(route('expenses.ai.advisor'));
        $respAdvisor->assertOk();

        $respQuery = $this->postJson(route('expenses.ai.query'), [
            'type' => 'explain_policy',
        ]);
        $respQuery->assertOk()
            ->assertJsonPath('is_advisory', true);
    }

    protected function createEmployee(string $tenantId, string $companyId, array $attributes = []): Employee
    {
        return Employee::create(array_merge([
            'tenant_id' => $tenantId,
            'company_id' => $companyId,
            'employee_code' => 'EMP-' . uniqid(),
            'employee_number' => 'EMP-' . rand(1000, 9999),
            'first_name' => 'Julian',
            'last_name' => 'Casablancas',
            'official_email' => 'julian.' . uniqid() . '@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ], $attributes));
    }
}
