<?php

namespace Database\Seeders;

use App\Domains\Organization\Models\Company;
use App\Domains\Payroll\Models\CompensationComponent;
use App\Domains\Payroll\Models\CompensationStructure;
use App\Domains\Payroll\Models\PayrollCalendar;
use App\Domains\Payroll\Models\PayrollLegalEntity;
use App\Domains\Payroll\Models\PayrollPeriod;
use App\Domains\Payroll\Models\PayrollPolicy;
use App\Domains\Payroll\Models\PayrollTaxRule;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Seeder;

class PayrollDefaultDataSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = Tenant::query()->get();
        foreach ($tenants as $tenant) {
            $this->seedTenantData($tenant->id);
        }
    }

    public function seedTenantData(string $tenantId): void
    {
        $company = Company::query()->where('tenant_id', $tenantId)->first();
        if (! $company) {
            $company = Company::query()->create([
                'tenant_id' => $tenantId,
                'name' => 'Default Corporation',
                'legal_name' => 'Default Corporation Inc.',
                'company_code' => 'CORP-' . substr($tenantId, 0, 4),
            ]);
        }

        // 1. Legal Entity
        $legalEntity = PayrollLegalEntity::query()->firstOrCreate(
            ['tenant_id' => $tenantId, 'code' => 'LEGAL-HQ'],
            [
                'company_id' => $company->id,
                'name' => 'HQ Corporate Legal Entity',
                'currency' => 'USD',
                'country' => 'USA',
                'region' => 'North America',
                'timezone' => 'America/New_York',
                'default_pay_frequency' => 'monthly',
                'is_active' => true,
            ]
        );

        // 2. Payroll Calendar
        $calendar = PayrollCalendar::query()->firstOrCreate(
            ['tenant_id' => $tenantId, 'code' => 'CAL-MONTHLY'],
            [
                'payroll_legal_entity_id' => $legalEntity->id,
                'name' => 'Standard Monthly Payroll Calendar',
                'frequency' => 'monthly',
                'period_start_day' => 1,
                'cutoff_day_offset' => 25,
                'pay_day_offset' => 5,
                'description' => 'Regular monthly payroll starting 1st of month with cutoff on 25th and payout on 5th of following month.',
                'is_default' => true,
                'is_active' => true,
            ]
        );

        // 3. Compensation Components
        $components = [
            [
                'code' => 'BASIC',
                'name' => 'Basic Salary',
                'component_type' => 'basic',
                'calculation_type' => 'fixed',
                'is_taxable' => true,
                'is_pensionable' => true,
                'is_overtime_eligible' => false,
                'is_recurring' => true,
                'priority_order' => 1,
            ],
            [
                'code' => 'HOUSING',
                'name' => 'Housing Allowance',
                'component_type' => 'allowance',
                'calculation_type' => 'percentage_of_basic',
                'percentage' => 30.00,
                'is_taxable' => true,
                'is_pensionable' => false,
                'is_overtime_eligible' => false,
                'is_recurring' => true,
                'priority_order' => 2,
            ],
            [
                'code' => 'TRANSPORT',
                'name' => 'Transport Allowance',
                'component_type' => 'allowance',
                'calculation_type' => 'percentage_of_basic',
                'percentage' => 10.00,
                'is_taxable' => true,
                'is_pensionable' => false,
                'is_overtime_eligible' => false,
                'is_recurring' => true,
                'priority_order' => 3,
            ],
            [
                'code' => 'MEDICAL',
                'name' => 'Medical Allowance',
                'component_type' => 'allowance',
                'calculation_type' => 'fixed',
                'default_amount' => 500.00,
                'is_taxable' => false,
                'is_pensionable' => false,
                'is_overtime_eligible' => false,
                'is_recurring' => true,
                'priority_order' => 4,
            ],
            [
                'code' => 'OVERTIME_REG',
                'name' => 'Regular Overtime',
                'component_type' => 'overtime',
                'calculation_type' => 'hourly_rate',
                'is_taxable' => true,
                'is_pensionable' => false,
                'is_overtime_eligible' => false,
                'is_recurring' => false,
                'priority_order' => 5,
            ],
            [
                'code' => 'BONUS_PERF',
                'name' => 'Performance Bonus',
                'component_type' => 'bonus',
                'calculation_type' => 'fixed',
                'is_taxable' => true,
                'is_pensionable' => false,
                'is_overtime_eligible' => false,
                'is_recurring' => false,
                'priority_order' => 6,
            ],
            [
                'code' => 'TAX_INC',
                'name' => 'Income Tax',
                'component_type' => 'tax',
                'calculation_type' => 'custom_rule',
                'is_taxable' => false,
                'is_pensionable' => false,
                'is_statutory' => true,
                'is_recurring' => true,
                'priority_order' => 1,
            ],
            [
                'code' => 'PENSION_EE',
                'name' => 'Employee Pension Contribution',
                'component_type' => 'pension',
                'calculation_type' => 'percentage_of_basic',
                'percentage' => 5.00,
                'is_taxable' => false,
                'is_pensionable' => false,
                'is_statutory' => true,
                'is_recurring' => true,
                'priority_order' => 2,
            ],
            [
                'code' => 'PENSION_ER',
                'name' => 'Employer Pension Contribution',
                'component_type' => 'employer_contribution',
                'calculation_type' => 'percentage_of_basic',
                'percentage' => 7.50,
                'is_taxable' => false,
                'is_pensionable' => false,
                'is_statutory' => true,
                'is_recurring' => true,
                'priority_order' => 10,
            ],
            [
                'code' => 'LOAN_DED',
                'name' => 'Loan Repayment',
                'component_type' => 'loan',
                'calculation_type' => 'fixed',
                'is_taxable' => false,
                'is_pensionable' => false,
                'is_recurring' => false,
                'priority_order' => 3,
            ],
            [
                'code' => 'ADVANCE_DED',
                'name' => 'Salary Advance Repayment',
                'component_type' => 'advance',
                'calculation_type' => 'fixed',
                'is_taxable' => false,
                'is_pensionable' => false,
                'is_recurring' => false,
                'priority_order' => 4,
            ],
        ];

        $componentMap = [];
        foreach ($components as $c) {
            $comp = CompensationComponent::query()->firstOrCreate(
                ['tenant_id' => $tenantId, 'code' => $c['code']],
                array_merge($c, ['tenant_id' => $tenantId, 'is_active' => true])
            );
            $componentMap[$c['code']] = $comp;
        }

        // 4. Standard Compensation Structure
        $structure = CompensationStructure::query()->firstOrCreate(
            ['tenant_id' => $tenantId, 'code' => 'STRUCT-STANDARD'],
            [
                'name' => 'Standard Professional Salary Structure',
                'currency' => 'USD',
                'description' => 'Standard corporate salary package with Basic, 30% Housing, 10% Transport, and $500 Medical.',
                'version' => 1,
                'is_active' => true,
            ]
        );

        $structureSequence = [
            ['code' => 'BASIC', 'seq' => 1, 'calc' => 'fixed', 'amt' => 0, 'pct' => null],
            ['code' => 'HOUSING', 'seq' => 2, 'calc' => 'percentage_of_basic', 'amt' => null, 'pct' => 30.00],
            ['code' => 'TRANSPORT', 'seq' => 3, 'calc' => 'percentage_of_basic', 'amt' => null, 'pct' => 10.00],
            ['code' => 'MEDICAL', 'seq' => 4, 'calc' => 'fixed', 'amt' => 500.00, 'pct' => null],
            ['code' => 'PENSION_EE', 'seq' => 5, 'calc' => 'percentage_of_basic', 'amt' => null, 'pct' => 5.00],
            ['code' => 'PENSION_ER', 'seq' => 6, 'calc' => 'percentage_of_basic', 'amt' => null, 'pct' => 7.50],
        ];

        foreach ($structureSequence as $item) {
            if (isset($componentMap[$item['code']])) {
                $structure->structureComponents()->firstOrCreate(
                    ['compensation_component_id' => $componentMap[$item['code']]->id],
                    [
                        'tenant_id' => $tenantId,
                        'calculation_type' => $item['calc'],
                        'default_amount' => $item['amt'],
                        'percentage' => $item['pct'],
                        'sequence' => $item['seq'],
                    ]
                );
            }
        }

        // 5. Progressive Tax Rule & Version (2026)
        $taxRule = PayrollTaxRule::query()->firstOrCreate(
            ['tenant_id' => $tenantId, 'code' => 'TAX-US-FED-2026'],
            [
                'name' => 'Standard Progressive Income Tax 2026',
                'country' => 'USA',
                'calculation_mode' => 'progressive_brackets',
                'is_active' => true,
            ]
        );

        $taxRule->versions()->firstOrCreate(
            ['version_name' => '2026.01'],
            [
                'tenant_id' => $tenantId,
                'effective_from' => '2026-01-01',
                'standard_exemption' => 1000.00,
                'tax_brackets' => [
                    ['min' => 0, 'max' => 2000, 'rate' => 0.05],       // 5% on first $2,000
                    ['min' => 2000, 'max' => 5000, 'rate' => 0.10],    // 10% on next $3,000
                    ['min' => 5000, 'max' => 10000, 'rate' => 0.20],   // 20% on next $5,000
                    ['min' => 10000, 'max' => null, 'rate' => 0.30],   // 30% above $10,000
                ],
                'is_active' => true,
            ]
        );

        // 6. Global Payroll Policy
        PayrollPolicy::query()->firstOrCreate(
            ['tenant_id' => $tenantId, 'code' => 'POL-GLOBAL-DEFAULT'],
            [
                'name' => 'Standard Global Payroll Policy',
                'proration_method' => 'calendar_days',
                'rounding_method' => 'half_up',
                'overtime_rate_multiplier' => 1.50,
                'weekend_overtime_multiplier' => 2.00,
                'holiday_overtime_multiplier' => 2.50,
                'variance_threshold_percentage' => 10.00,
                'deduction_priority_order' => ['tax', 'statutory', 'court_order', 'loan', 'advance', 'benefit', 'other'],
                'is_default' => true,
                'is_active' => true,
            ]
        );
    }
}
