<?php

namespace Database\Seeders;

use App\Domains\Benefits\Enums\BenefitCategoryType;
use App\Domains\Benefits\Enums\InterestMethod;
use App\Domains\Benefits\Enums\LoanProductType;
use App\Domains\Benefits\Enums\RetirementPlanType;
use App\Domains\Benefits\Models\BenefitCategory;
use App\Domains\Benefits\Models\BenefitEnrollmentWindow;
use App\Domains\Benefits\Models\BenefitPlan;
use App\Domains\Benefits\Models\BenefitProvider;
use App\Domains\Benefits\Models\FinancialWellnessProgram;
use App\Domains\Benefits\Models\LoanProduct;
use App\Domains\Benefits\Models\RetirementPlan;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Seeder;

class BenefitsDefaultDataSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::first();
        if (! $tenant) {
            return;
        }

        // 1. Benefit Categories
        $healthCat = BenefitCategory::firstOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'HEALTH-INS'],
            [
                'name' => 'Health & Medical Insurance',
                'category_type' => BenefitCategoryType::HEALTH_INSURANCE->value,
                'description' => 'Comprehensive inpatient, outpatient, and prescription drug coverage.',
                'is_statutory' => false,
                'is_active' => true,
            ]
        );

        $lifeCat = BenefitCategory::firstOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'LIFE-INS'],
            [
                'name' => 'Group Term Life Insurance',
                'category_type' => BenefitCategoryType::LIFE_INSURANCE->value,
                'description' => 'Term life and accidental death and dismemberment protection.',
                'is_statutory' => false,
                'is_active' => true,
            ]
        );

        $retirementCat = BenefitCategory::firstOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'RET-SAVINGS'],
            [
                'name' => 'Retirement & Provident Fund',
                'category_type' => BenefitCategoryType::RETIREMENT->value,
                'description' => 'Employer-sponsored retirement savings and matching plans.',
                'is_statutory' => true,
                'is_active' => true,
            ]
        );

        // 2. Benefit Provider
        $provider = BenefitProvider::firstOrCreate(
            ['tenant_id' => $tenant->id, 'provider_code' => 'AETNA-GLOBAL'],
            [
                'name' => 'Aetna Global Health & Life',
                'provider_type' => 'insurance_company',
                'contract_number' => 'CTR-2026-AET-99',
                'policy_number' => 'POL-GRP-90812',
                'contact_person' => 'Rachel Green',
                'contact_email' => 'corporate-support@aetna-benefits.com',
                'contract_start_date' => '2026-01-01',
                'contract_expiry_date' => '2027-12-31',
                'is_active' => true,
            ]
        );

        // 3. Benefit Plans
        BenefitPlan::firstOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'AETNA-GOLD-HLTH'],
            [
                'benefit_category_id' => $healthCat->id,
                'benefit_provider_id' => $provider->id,
                'name' => 'Aetna Gold Comprehensive Health',
                'benefit_type' => 'health_insurance',
                'coverage_level' => 'family',
                'currency' => 'USD',
                'employee_cost' => 120.0000,
                'employer_cost' => 450.0000,
                'annual_limit' => 100000.0000,
                'monthly_limit' => 10000.0000,
                'waiting_period_days' => 30,
                'requires_beneficiary' => false,
                'requires_dependents' => true,
                'version' => 1,
                'effective_from' => '2026-01-01',
                'status' => 'active',
            ]
        );

        // 4. Open Enrollment Window
        BenefitEnrollmentWindow::firstOrCreate(
            ['tenant_id' => $tenant->id, 'plan_year' => 2027],
            [
                'name' => 'Annual Open Enrollment 2027',
                'start_date' => '2026-11-01',
                'close_date' => '2026-11-30',
                'effective_date' => '2027-01-01',
                'status' => 'open',
                'allow_late_enrollment' => false,
                'description' => 'Annual benefit election window for all eligible regular employees.',
            ]
        );

        // 5. Retirement Plan
        RetirementPlan::firstOrCreate(
            ['tenant_id' => $tenant->id, 'plan_code' => '401K-CORP'],
            [
                'name' => 'Corporate 401(k) / Provident Fund',
                'plan_type' => RetirementPlanType::DEFINED_CONTRIBUTION->value,
                'currency' => 'USD',
                'provider' => 'Vanguard Institutional',
                'country' => 'USA',
                'default_employee_rate' => 5.0000,
                'default_employer_match_rate' => 100.0000,
                'max_employer_contribution_rate' => 6.0000,
                'contribution_base' => 'basic_salary',
                'vesting_type' => 'graded',
                'is_mandatory' => false,
                'is_active' => true,
                'version' => 1,
            ]
        );

        // 6. Loan Products
        LoanProduct::firstOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'PERSONAL-LOAN'],
            [
                'name' => 'Employee Personal Assistance Loan',
                'loan_type' => LoanProductType::PERSONAL->value,
                'currency' => 'USD',
                'minimum_amount' => 500.0000,
                'maximum_amount' => 10000.0000,
                'max_installments' => 24,
                'interest_rate_annual' => 3.5000,
                'interest_method' => InterestMethod::REDUCING_BALANCE->value,
                'min_service_months' => 6,
                'max_salary_multiple' => 3.00,
                'max_monthly_deduction_ratio' => 35.00,
                'deduction_priority' => 3,
                'requires_guarantor' => false,
                'is_active' => true,
            ]
        );

        LoanProduct::firstOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'SALARY-ADVANCE-PROD'],
            [
                'name' => 'Emergency Salary Advance',
                'loan_type' => LoanProductType::SALARY_ADVANCE->value,
                'currency' => 'USD',
                'minimum_amount' => 100.0000,
                'maximum_amount' => 2000.0000,
                'max_installments' => 3,
                'interest_rate_annual' => 0.0000,
                'interest_method' => InterestMethod::ZERO_INTEREST->value,
                'min_service_months' => 3,
                'max_salary_multiple' => 0.75,
                'max_monthly_deduction_ratio' => 50.00,
                'deduction_priority' => 1,
                'is_active' => true,
            ]
        );

        // 7. Financial Wellness Program
        $prog = FinancialWellnessProgram::firstOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'FIN-FREEDOM'],
            [
                'name' => 'Financial Freedom & Debt Coaching',
                'program_type' => 'debt_counseling',
                'description' => 'Advisory sessions and budgeting workshops for employees.',
                'partner_organization' => 'SmartMoney Advisory Group',
                'start_date' => '2026-01-01',
                'is_active' => true,
            ]
        );

        $prog->resources()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'title' => 'Building a 3-Month Emergency Fund'],
            [
                'resource_type' => 'article',
                'summary' => 'Step-by-step guidance on setting up an automated emergency savings reserve.',
                'is_featured' => true,
            ]
        );
    }
}
