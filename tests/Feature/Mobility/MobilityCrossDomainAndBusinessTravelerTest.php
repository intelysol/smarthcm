<?php

namespace Tests\Feature\Mobility;

use App\Domains\Employee\Models\Employee;
use App\Domains\Mobility\Models\MobilityAssignment;
use App\Domains\Mobility\Models\MobilityBusinessTraveler;
use App\Domains\Mobility\Services\BusinessTravelerService;
use App\Domains\Mobility\Services\MobilityCrossDomainCoordinator;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class MobilityCrossDomainAndBusinessTravelerTest extends TestCase
{
    use RefreshDatabase;

    public function test_cross_domain_links_coordination(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-5001',
            'employee_number' => 'EMP-5001',
            'first_name' => 'Sophia',
            'last_name' => 'Loren',
            'official_email' => 'sophia@example.com',
            'employment_status' => 'active',
            'joining_date' => '2025-01-01',
        ]);

        $assignment = MobilityAssignment::create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'assignment_number' => 'ASN-CROSS-001',
            'home_country' => 'United States',
            'host_country' => 'France',
            'home_company_id' => $company->id,
            'host_company_id' => $company->id,
            'start_date' => '2026-10-01',
            'planned_end_date' => '2027-09-30',
        ]);

        $coordinator = app(MobilityCrossDomainCoordinator::class);

        // 1. Compliance link (Visa / Work Permit from Epic 2.33)
        $complianceLink = $coordinator->linkComplianceRecord(
            $assignment,
            'visa',
            (string) Str::uuid(),
            '2027-10-01'
        );
        $this->assertDatabaseHas('hcm_mobility_compliance_links', [
            'id' => $complianceLink->id,
            'assignment_id' => $assignment->id,
            'compliance_type' => 'visa',
        ]);

        // 2. Document link (Assignment Letter from Epic 2.30)
        $docLink = $coordinator->linkDocument(
            $assignment,
            'assignment_letter',
            (string) Str::uuid()
        );
        $this->assertDatabaseHas('hcm_mobility_document_links', [
            'id' => $docLink->id,
            'assignment_id' => $assignment->id,
            'document_type' => 'assignment_letter',
        ]);

        // 3. Expense link (Travel Request from Epic 2.38)
        $expenseLink = $coordinator->linkExpenseEntity(
            $assignment,
            'travel_request',
            (string) Str::uuid(),
            4500.00,
            'EUR'
        );
        $this->assertDatabaseHas('hcm_mobility_expense_links', [
            'id' => $expenseLink->id,
            'assignment_id' => $assignment->id,
            'amount' => 4500.00,
        ]);

        // 4. Benefit link (International Health Plan from Epic 2.37)
        $benefitLink = $coordinator->linkBenefitPlan(
            $assignment,
            (string) Str::uuid(),
            null,
            'international_health'
        );
        $this->assertDatabaseHas('hcm_mobility_benefit_links', [
            'id' => $benefitLink->id,
            'assignment_id' => $assignment->id,
            'benefit_category' => 'international_health',
        ]);

        // 5. Compensation link (Allowances from Epic 2.36)
        $compLink = $coordinator->linkCompensationPackage($assignment, [
            'home_salary' => 120000.00,
            'host_salary' => 110000.00,
            'mobility_allowance' => 15000.00,
            'housing_allowance' => 30000.00,
            'hardship_allowance' => 5000.00,
        ]);
        $this->assertDatabaseHas('hcm_mobility_compensation_links', [
            'id' => $compLink->id,
            'assignment_id' => $assignment->id,
            'mobility_allowance' => 15000.00,
        ]);
    }

    public function test_business_traveler_registration_and_pe_risk_assessment(): void
    {
        Carbon::setTestNow('2026-09-01 10:00:00');

        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-5002',
            'employee_number' => 'EMP-5002',
            'first_name' => 'Mark',
            'last_name' => 'Ruffalo',
            'official_email' => 'mark@example.com',
            'employment_status' => 'active',
            'joining_date' => '2025-01-01',
        ]);

        $travelerService = app(BusinessTravelerService::class);

        // Trip 1: 10 days in Canada -> Low Risk
        $trip1 = $travelerService->registerTrip([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'destination_country' => 'Canada',
            'destination_city' => 'Toronto',
            'start_date' => '2026-09-05',
            'end_date' => '2026-09-14',
            'business_purpose' => 'Quarterly Planning Workshop',
            'visa_required' => false,
            'visa_cleared' => true,
        ]);

        $this->assertEquals(10, $trip1->trip_days);
        $this->assertEquals('low', $trip1->compliance_risk_level);

        // Trip 2: 30 days in Canada -> Cumulative 40 days -> Medium Risk
        $trip2 = $travelerService->registerTrip([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'destination_country' => 'Canada',
            'destination_city' => 'Vancouver',
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-30',
            'business_purpose' => 'Implementation rollout',
        ]);

        $this->assertEquals('medium', $trip2->compliance_risk_level);

        // Trip 3: 60 days in Canada -> Cumulative 100 days -> High Risk (PE / Tax residency concern)
        $trip3 = $travelerService->registerTrip([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'destination_country' => 'Canada',
            'destination_city' => 'Montreal',
            'start_date' => '2026-11-05',
            'end_date' => '2027-01-03',
            'business_purpose' => 'Extended engineering contract supervision',
        ]);

        $this->assertEquals('high', $trip3->compliance_risk_level);
    }
}
