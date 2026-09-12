<?php

namespace Tests\Feature\Expenses;

use App\Domains\Employee\Models\Employee;
use App\Domains\Expenses\Models\ExpenseCategory;
use App\Domains\Expenses\Models\MileageRate;
use App\Domains\Expenses\Models\PerDiemRate;
use App\Domains\Expenses\Services\ExpenseClaimService;
use App\Domains\Expenses\Services\MileageService;
use App\Domains\Expenses\Services\PerDiemService;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MileageAndPerDiemCalculationTest extends TestCase
{
    use RefreshDatabase;

    public function test_mileage_and_per_diem_with_partial_days_and_meal_deductions(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $employee = $this->createEmployee($tenant->id, $company->id);

        $mileageCat = ExpenseCategory::create([
            'tenant_id' => $tenant->id,
            'code' => 'MILEAGE',
            'name' => 'Mileage Reimbursement',
            'category_type' => 'mileage',
            'receipt_required' => false,
        ]);

        $perDiemCat = ExpenseCategory::create([
            'tenant_id' => $tenant->id,
            'code' => 'PER_DIEM',
            'name' => 'Per Diem Allowance',
            'category_type' => 'per_diem',
            'receipt_required' => false,
        ]);

        // 1. Mileage Calculation: 125 km @ $0.60/km = $75.00
        MileageRate::create([
            'tenant_id' => $tenant->id,
            'vehicle_type' => 'standard_car',
            'rate_per_unit' => 0.6000,
            'unit' => 'km',
            'currency' => 'USD',
            'effective_from' => '2026-01-01',
            'is_active' => true,
        ]);

        $mileageService = app(MileageService::class);
        $rate = $mileageService->getActiveRate($tenant->id, 'standard_car');
        $mileageTotal = $mileageService->calculateMileageAmount(125, $rate);

        $this->assertEquals(0.6000, $rate);
        $this->assertEquals(75.0000, $mileageTotal);

        // 2. Per Diem Calculation:
        // Rate: $100/day
        // Departure day factor: 75% -> Gross $75.00
        // Breakfast provided: -20% -> Net $60.00
        $perDiemRate = PerDiemRate::create([
            'tenant_id' => $tenant->id,
            'destination_type' => 'domestic',
            'daily_rate' => 100.0000,
            'currency' => 'USD',
            'departure_day_percentage' => 75.00,
            'breakfast_deduction_percentage' => 20.00,
            'effective_from' => '2026-01-01',
            'is_active' => true,
        ]);

        $perDiemService = app(PerDiemService::class);
        $calc = $perDiemService->calculatePerDiemAmount($perDiemRate, 1, true, false, true, false, false);

        $this->assertEquals(75.0000, $calc['gross_amount']);
        $this->assertEquals(15.0000, $calc['deduction_amount']); // 20% of $75 = $15
        $this->assertEquals(60.0000, $calc['net_eligible_amount']);

        // 3. Attach both to an Expense Claim
        $claimService = app(ExpenseClaimService::class);
        $claim = $claimService->createClaim($employee, ['title' => 'Mileage & Per Diem']);

        $claimService->addLine($claim, [
            'expense_category_id' => $mileageCat->id,
            'expense_date' => '2026-10-01',
            'description' => '125km client site visit',
            'original_amount' => $mileageTotal,
            'is_mileage' => true,
            'mileage_distance' => 125,
            'mileage_rate' => $rate,
        ]);

        $claimService->addLine($claim, [
            'expense_category_id' => $perDiemCat->id,
            'expense_date' => '2026-10-01',
            'description' => 'Departure day per diem (breakfast provided)',
            'original_amount' => $calc['net_eligible_amount'],
            'is_per_diem' => true,
            'per_diem_days' => 1,
            'per_diem_rate' => $calc['daily_rate'],
            'meal_deductions' => $calc['deduction_amount'],
        ]);

        $this->assertEquals(135.0000, (float) $claim->fresh()->claimed_total); // 75 + 60 = 135
        $this->assertEquals(135.0000, (float) $claim->fresh()->approved_total);
    }

    protected function createEmployee(string $tenantId, string $companyId, array $attributes = []): Employee
    {
        return Employee::create(array_merge([
            'tenant_id' => $tenantId,
            'company_id' => $companyId,
            'employee_code' => 'EMP-' . uniqid(),
            'employee_number' => 'EMP-' . rand(1000, 9999),
            'first_name' => 'Daniel',
            'last_name' => 'Defoe',
            'official_email' => 'daniel.' . uniqid() . '@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ], $attributes));
    }
}
