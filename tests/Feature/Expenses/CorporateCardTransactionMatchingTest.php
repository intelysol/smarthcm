<?php

namespace Tests\Feature\Expenses;

use App\Domains\Employee\Models\Employee;
use App\Domains\Expenses\Models\CorporateCard;
use App\Domains\Expenses\Models\ExpenseCategory;
use App\Domains\Expenses\Services\CorporateCardService;
use App\Domains\Expenses\Services\ExpenseClaimService;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CorporateCardTransactionMatchingTest extends TestCase
{
    use RefreshDatabase;

    public function test_corporate_card_issuance_and_transaction_matching(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $employee = $this->createEmployee($tenant->id, $company->id);
        $financeUser = User::factory()->create(['tenant_id' => $tenant->id]);

        $cat = ExpenseCategory::create([
            'tenant_id' => $tenant->id,
            'code' => 'AIRFARE',
            'name' => 'Flights',
            'category_type' => 'transportation',
        ]);

        $cardService = app(CorporateCardService::class);
        $claimService = app(ExpenseClaimService::class);

        // 1. Assign masked corporate card
        $card = $cardService->assignCard($employee, [
            'card_number' => '4111222233334444',
            'card_provider' => 'Visa Commercial',
        ]);

        $this->assertEquals('XXXX-XXXX-XXXX-4444', $card->card_masked_number);
        $this->assertEquals('active', $card->status);

        // 2. Import Bank Transaction Feed
        $tx = $cardService->importTransaction($card, [
            'merchant_name' => 'United Airlines Flight 402',
            'amount' => 450.0000,
            'currency' => 'USD',
            'transaction_date' => '2026-09-15 10:30:00',
        ]);

        $this->assertFalse($tx->is_matched);
        $this->assertEquals('unmatched', $tx->match_status);

        // 3. Employee creates claim line
        $claim = $claimService->createClaim($employee, ['title' => 'Flight to Conference']);
        $line = $claimService->addLine($claim, [
            'expense_category_id' => $cat->id,
            'expense_date' => '2026-09-15',
            'merchant' => 'United Airlines',
            'description' => 'Flight 402 Ticket',
            'original_amount' => 450.0000,
            'payment_method' => 'corporate_card',
        ]);

        // 4. Match Card Transaction to Claim Line
        $match = $cardService->matchTransactionToClaimLine($tx, $line, $financeUser);

        $this->assertTrue($tx->fresh()->is_matched);
        $this->assertEquals('matched', $tx->fresh()->match_status);
        $this->assertEquals($tx->id, $line->fresh()->corporate_card_transaction_id);
    }

    protected function createEmployee(string $tenantId, string $companyId, array $attributes = []): Employee
    {
        return Employee::create(array_merge([
            'tenant_id' => $tenantId,
            'company_id' => $companyId,
            'employee_code' => 'EMP-' . uniqid(),
            'employee_number' => 'EMP-' . rand(1000, 9999),
            'first_name' => 'Hedy',
            'last_name' => 'Lamarr',
            'official_email' => 'hedy.' . uniqid() . '@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ], $attributes));
    }
}
