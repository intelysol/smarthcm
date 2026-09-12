<?php

namespace Tests\Feature\PersonalData;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\PersonalData\Services\DuplicateDetectionService;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DuplicateDetectionHeuristicsTest extends TestCase
{
    use RefreshDatabase;

    public function test_advisory_duplicate_detection_heuristics(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $emp1 = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-ORIG-01',
            'employee_number' => 'EMP-ORIG-01',
            'first_name' => 'William',
            'last_name' => 'Harrison',
            'national_id' => '999-88-7777',
            'personal_email' => 'wharrison@presidents.gov',
            'mobile' => '+15550001111',
            'date_of_birth' => '1973-02-09',
            'employment_status' => 'active',
            'joining_date' => now()->toDateString(),
        ]);

        $emp2 = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-DUP-01',
            'employee_number' => 'EMP-DUP-01',
            'first_name' => 'William',
            'last_name' => 'Harrison',
            'national_id' => '999-88-7777', // Same CNIC/SSN
            'personal_email' => 'other@domain.com',
            'employment_status' => 'active',
            'joining_date' => now()->toDateString(),
        ]);

        $service = app(DuplicateDetectionService::class);
        $duplicates = $service->detectDuplicates($tenant->id, $emp1->id);

        $this->assertNotEmpty($duplicates);
        $match = $duplicates->first();

        $this->assertEquals($emp2->id, $match['candidate_employee_id']);
        $this->assertEquals(95, $match['confidence_score']);
        $this->assertTrue($match['is_advisory']);
        $this->assertFalse($match['automated_merge_allowed']);
        $this->assertStringContainsString('National ID match', $match['match_reasons'][0]);
    }
}
