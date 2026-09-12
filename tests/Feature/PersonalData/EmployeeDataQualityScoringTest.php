<?php

namespace Tests\Feature\PersonalData;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\PersonalData\Models\HcmEmployeeDataQualityIssue;
use App\Domains\PersonalData\Services\AddressService;
use App\Domains\PersonalData\Services\EmergencyContactService;
use App\Domains\PersonalData\Services\EmployeeDataQualityService;
use App\Domains\PersonalData\Services\EmployeeIdentifierService;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeDataQualityScoringTest extends TestCase
{
    use RefreshDatabase;

    public function test_quality_dimensions_and_issue_generation(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        // Sparse employee (missing DOB, address, emergency contact, identifier)
        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-QLT-01',
            'employee_number' => 'EMP-QLT-01',
            'first_name' => 'Martin',
            'last_name' => 'Van Buren',
            'official_email' => 'martin@example.com',
            'employment_status' => 'active',
            'joining_date' => now()->toDateString(),
        ]);

        $service = app(EmployeeDataQualityService::class);

        // 1. Initial calculation on sparse employee
        $result = $service->calculateQuality($employee->id);
        $this->assertLessThan(70, $result->overall_score);
        $this->assertGreaterThan(0, $result->issues_count);

        $issues = HcmEmployeeDataQualityIssue::where('employee_id', $employee->id)->pluck('issue_code')->toArray();
        $this->assertContains('MISSING_DOB', $issues);
        $this->assertContains('MISSING_CURRENT_ADDRESS', $issues);
        $this->assertContains('MISSING_PRIMARY_EMERGENCY_CONTACT', $issues);

        // 2. Add complete data
        $employee->update([
            'date_of_birth' => '1985-05-15',
            'gender' => 'male',
        ]);
        app(AddressService::class)->addAddress($employee->id, [
            'address_type' => 'home',
            'address_line_1' => '123 Kinderhook Lane',
            'city' => 'Albany',
            'country' => 'USA',
            'is_current' => true,
        ]);
        app(EmergencyContactService::class)->addContact($employee->id, [
            'name' => 'Hannah Van Buren',
            'relationship' => 'Spouse',
            'primary_phone' => '+15559998888',
        ]);
        app(EmployeeIdentifierService::class)->addIdentifier($employee->id, [
            'identifier_type' => 'national_id',
            'identifier_value' => '123456789',
            'is_primary' => true,
        ]);

        // 3. Recalculate
        $updatedResult = $service->calculateQuality($employee->id);
        $this->assertGreaterThan($result->overall_score, $updatedResult->overall_score);
        $this->assertEquals(100.00, (float) $updatedResult->completeness_score);

        // 4. Tenant summary
        $summary = $service->getTenantQualitySummary($tenant->id);
        $this->assertEquals(1, $summary['total_employees_assessed']);
        $this->assertGreaterThan(0, $summary['avg_overall_score']);
    }
}
