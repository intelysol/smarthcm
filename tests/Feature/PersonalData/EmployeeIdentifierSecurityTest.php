<?php

namespace Tests\Feature\PersonalData;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\PersonalData\Models\HcmEmployeeIdentifier;
use App\Domains\PersonalData\Services\EmployeeIdentifierService;
use App\Domains\PersonalData\Services\PersonalDataSecurityService;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EmployeeIdentifierSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_identifier_encryption_masking_and_authorization(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-ID-01',
            'employee_number' => 'EMP-ID-01',
            'first_name' => 'James',
            'last_name' => 'Madison',
            'official_email' => 'james.madison@example.com',
            'employment_status' => 'active',
            'joining_date' => now()->toDateString(),
        ]);

        $service = app(EmployeeIdentifierService::class);
        $rawCnic = '35202-1234567-1';

        // 1. Add identifier
        $identifier = $service->addIdentifier($employee->id, [
            'identifier_type' => 'national_id',
            'identifier_value' => $rawCnic,
            'issuing_country' => 'Pakistan',
            'is_primary' => true,
        ]);

        // 2. Verify encrypted at rest in raw DB
        $rawRecord = DB::table('hcm_employee_identifiers')->where('id', $identifier->id)->first();
        $this->assertNotEquals($rawCnic, $rawRecord->identifier_value);
        $this->assertStringStartsWith('ey', $rawRecord->identifier_value); // Laravel encrypted payload signature

        // 3. Verify masked value was generated
        $this->assertStringContainsString('*', $rawRecord->masked_value);

        // 4. Verify Core HR employee national_id was synced
        $employee->refresh();
        $this->assertEquals($rawCnic, $employee->national_id);

        // 5. Test access control: regular user sees masked value
        $regularUser = User::factory()->create(['tenant_id' => $tenant->id]);
        $unauthorizedList = $service->getIdentifiers($employee->id, $regularUser);
        $this->assertStringContainsString('*', $unauthorizedList->first()->identifier_value);

        // 6. Test access control: admin user sees unmasked value
        $adminUser = User::factory()->create(['tenant_id' => $tenant->id, 'is_platform_admin' => true]);
        $adminList = $service->getIdentifiers($employee->id, $adminUser);
        $this->assertEquals($rawCnic, $adminList->first()->identifier_value);
    }
}
