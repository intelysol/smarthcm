<?php

namespace Database\Seeders;

use App\Domains\Expenses\Models\ExpenseCategory;
use App\Domains\Expenses\Models\ExpenseExchangeRate;
use App\Domains\Expenses\Models\ExpensePolicy;
use App\Domains\Expenses\Models\ExpensePolicyVersion;
use App\Domains\Expenses\Models\MileageRate;
use App\Domains\Expenses\Models\PerDiemRate;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Seeder;

class ExpenseDefaultDataSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = Tenant::all();
        foreach ($tenants as $tenant) {
            $this->seedTenantData($tenant->id);
        }
    }

    public function seedTenantData(string $tenantId): void
    {
        // 1. Categories
        $categories = [
            ['code' => 'MEALS', 'name' => 'Meals & Sustenance', 'category_type' => 'meals', 'max_amount' => 5000.0000, 'receipt_threshold' => 2000.0000, 'receipt_required' => true, 'accounting_code' => 'GL-6101'],
            ['code' => 'HOTEL', 'name' => 'Hotel & Accommodation', 'category_type' => 'accommodation', 'max_amount' => 20000.0000, 'receipt_threshold' => 0.0000, 'receipt_required' => true, 'accounting_code' => 'GL-6102'],
            ['code' => 'FLIGHT', 'name' => 'Airfare & Flights', 'category_type' => 'transportation', 'max_amount' => 150000.0000, 'receipt_threshold' => 0.0000, 'receipt_required' => true, 'accounting_code' => 'GL-6103'],
            ['code' => 'TAXI', 'name' => 'Taxi & Rideshare', 'category_type' => 'transportation', 'max_amount' => 5000.0000, 'receipt_threshold' => 1000.0000, 'receipt_required' => true, 'accounting_code' => 'GL-6104'],
            ['code' => 'MILEAGE', 'name' => 'Personal Vehicle Mileage', 'category_type' => 'mileage', 'max_amount' => null, 'receipt_threshold' => 0.0000, 'receipt_required' => false, 'accounting_code' => 'GL-6105'],
            ['code' => 'PER_DIEM', 'name' => 'Daily Per Diem Allowance', 'category_type' => 'per_diem', 'max_amount' => null, 'receipt_threshold' => 0.0000, 'receipt_required' => false, 'accounting_code' => 'GL-6106'],
            ['code' => 'CLIENT_ENT', 'name' => 'Client Entertainment', 'category_type' => 'entertainment', 'max_amount' => 25000.0000, 'receipt_threshold' => 0.0000, 'receipt_required' => true, 'accounting_code' => 'GL-6107'],
            ['code' => 'OFFICE_SUP', 'name' => 'Office & Project Supplies', 'category_type' => 'supplies', 'max_amount' => 10000.0000, 'receipt_threshold' => 1500.0000, 'receipt_required' => true, 'accounting_code' => 'GL-6108'],
            ['code' => 'COMM', 'name' => 'Communication & Roaming', 'category_type' => 'communication', 'max_amount' => 8000.0000, 'receipt_threshold' => 1000.0000, 'receipt_required' => true, 'accounting_code' => 'GL-6109'],
            ['code' => 'TRAINING', 'name' => 'Training & Conferences', 'category_type' => 'education', 'max_amount' => 50000.0000, 'receipt_threshold' => 0.0000, 'receipt_required' => true, 'accounting_code' => 'GL-6110'],
        ];

        foreach ($categories as $cat) {
            ExpenseCategory::updateOrCreate(
                ['tenant_id' => $tenantId, 'code' => $cat['code']],
                array_merge($cat, ['tenant_id' => $tenantId, 'is_reimbursable' => true, 'is_active' => true])
            );
        }

        // 2. Global Expense Policy
        $policy = ExpensePolicy::updateOrCreate(
            ['tenant_id' => $tenantId, 'policy_code' => 'GLOBAL-EXP-2026'],
            [
                'tenant_id' => $tenantId,
                'name' => 'Standard Corporate Expense Policy 2026',
                'description' => 'Standard corporate travel, meal caps, receipt rules and reimbursement thresholds.',
                'effective_from' => '2026-01-01',
                'status' => 'active',
                'current_version' => 1,
            ]
        );

        ExpensePolicyVersion::updateOrCreate(
            ['tenant_id' => $tenantId, 'expense_policy_id' => $policy->id, 'version_number' => 1],
            [
                'tenant_id' => $tenantId,
                'expense_policy_id' => $policy->id,
                'version_number' => 1,
                'effective_from' => '2026-01-01',
                'daily_meal_limit' => 5000.0000,
                'daily_hotel_limit' => 20000.0000,
                'receipt_required_threshold' => 2000.0000,
                'allow_policy_override' => true,
                'is_active' => true,
            ]
        );

        // 3. Mileage Rates
        MileageRate::updateOrCreate(
            ['tenant_id' => $tenantId, 'vehicle_type' => 'standard_car'],
            [
                'tenant_id' => $tenantId,
                'vehicle_type' => 'standard_car',
                'rate_per_unit' => 100.0000, // 100/km
                'unit' => 'km',
                'currency' => 'USD',
                'effective_from' => '2026-01-01',
                'is_active' => true,
            ]
        );

        // 4. Per Diem Rates
        PerDiemRate::updateOrCreate(
            ['tenant_id' => $tenantId, 'destination_type' => 'domestic'],
            [
                'tenant_id' => $tenantId,
                'destination_type' => 'domestic',
                'daily_rate' => 100.0000,
                'currency' => 'USD',
                'departure_day_percentage' => 75.00,
                'return_day_percentage' => 75.00,
                'breakfast_deduction_percentage' => 20.00,
                'lunch_deduction_percentage' => 30.00,
                'dinner_deduction_percentage' => 30.00,
                'effective_from' => '2026-01-01',
                'is_active' => true,
            ]
        );

        // 5. Exchange Rates
        ExpenseExchangeRate::updateOrCreate(
            ['tenant_id' => $tenantId, 'from_currency' => 'EUR', 'to_currency' => 'USD'],
            [
                'tenant_id' => $tenantId,
                'from_currency' => 'EUR',
                'to_currency' => 'USD',
                'rate' => 1.080000,
                'effective_date' => '2026-01-01',
                'source' => 'finance_system',
                'is_active' => true,
            ]
        );
    }
}
