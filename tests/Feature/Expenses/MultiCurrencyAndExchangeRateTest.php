<?php

namespace Tests\Feature\Expenses;

use App\Domains\Employee\Models\Employee;
use App\Domains\Expenses\Models\ExpenseCategory;
use App\Domains\Expenses\Models\ExpenseExchangeRate;
use App\Domains\Expenses\Services\ExchangeRateService;
use App\Domains\Expenses\Services\ExpenseClaimService;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiCurrencyAndExchangeRateTest extends TestCase
{
    use RefreshDatabase;

    public function test_multi_currency_conversion_and_historical_rate_immutability(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $employee = $this->createEmployee($tenant->id, $company->id);

        $hotelCategory = ExpenseCategory::create([
            'tenant_id' => $tenant->id,
            'code' => 'HOTEL',
            'name' => 'Hotel',
            'category_type' => 'accommodation',
        ]);

        // Rate on 2026-06-01: 1 EUR = 1.080000 USD
        ExpenseExchangeRate::create([
            'tenant_id' => $tenant->id,
            'from_currency' => 'EUR',
            'to_currency' => 'USD',
            'rate' => 1.080000,
            'effective_date' => '2026-06-01',
            'source' => 'finance_ecb',
            'is_active' => true,
        ]);

        // Rate changes on 2026-08-01: 1 EUR = 1.150000 USD
        ExpenseExchangeRate::create([
            'tenant_id' => $tenant->id,
            'from_currency' => 'EUR',
            'to_currency' => 'USD',
            'rate' => 1.150000,
            'effective_date' => '2026-08-01',
            'source' => 'finance_ecb',
            'is_active' => true,
        ]);

        $exchangeService = app(ExchangeRateService::class);
        $rateJune = $exchangeService->getExchangeRate($tenant->id, 'EUR', 'USD', '2026-06-15');
        $rateAugust = $exchangeService->getExchangeRate($tenant->id, 'EUR', 'USD', '2026-08-15');

        $this->assertEquals(1.080000, $rateJune);
        $this->assertEquals(1.150000, $rateAugust);

        // Claim with June Expense: EUR 500 @ 1.08 = USD 540.00
        $claimService = app(ExpenseClaimService::class);
        $claim = $claimService->createClaim($employee, [
            'title' => 'Paris Hotel Claim',
            'currency' => 'USD',
            'claim_date' => '2026-06-20',
        ]);

        $line = $claimService->addLine($claim, [
            'expense_category_id' => $hotelCategory->id,
            'expense_date' => '2026-06-15',
            'description' => 'Hotel Le Marais Paris',
            'original_amount' => 500.0000,
            'original_currency' => 'EUR',
        ]);

        $this->assertEquals('EUR', $line->original_currency);
        $this->assertEquals(500.0000, (float) $line->original_amount);
        $this->assertEquals(1.080000, (float) $line->exchange_rate);
        $this->assertEquals(540.0000, (float) $line->base_amount);
        $this->assertEquals(540.0000, (float) $claim->fresh()->claimed_total);
    }

    protected function createEmployee(string $tenantId, string $companyId, array $attributes = []): Employee
    {
        return Employee::create(array_merge([
            'tenant_id' => $tenantId,
            'company_id' => $companyId,
            'employee_code' => 'EMP-' . uniqid(),
            'employee_number' => 'EMP-' . rand(1000, 9999),
            'first_name' => 'Emma',
            'last_name' => 'Watson',
            'official_email' => 'emma.' . uniqid() . '@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ], $attributes));
    }
}
