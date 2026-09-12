<?php

namespace Tests\Feature\PersonalData;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\PersonalData\Services\PersonalDataService;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonalDataSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_personal_data_get_or_create_and_core_hr_sync(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-SYNC-01',
            'employee_number' => 'EMP-SYNC-01',
            'first_name' => 'Alexander',
            'last_name' => 'Hamilton',
            'official_email' => 'alex@treasury.gov',
            'employment_status' => 'active',
            'joining_date' => now()->toDateString(),
        ]);

        $service = app(PersonalDataService::class);

        // 1. Get or create record
        $personalData = $service->getOrCreate($employee->id, $tenant->id);
        $this->assertNotNull($personalData);
        $this->assertEquals('Alexander', $personalData->first_name);
        $this->assertEquals('Hamilton', $personalData->last_name);

        // 2. Update personal data and verify Core HR sync
        $updated = $service->update($employee->id, [
            'preferred_name' => 'Alex',
            'marital_status' => 'married',
            'blood_group' => 'O+',
            'personal_email' => 'alexander.hamilton@gmail.com',
            'personal_phone' => '+15551234567',
        ]);

        $this->assertEquals('Alex', $updated->preferred_name);
        $this->assertEquals('married', $updated->marital_status);

        $employee->refresh();
        $this->assertEquals('Alex', $employee->preferred_name);
        $this->assertEquals('married', $employee->marital_status);
        $this->assertEquals('O+', $employee->blood_group);
        $this->assertEquals('alexander.hamilton@gmail.com', $employee->personal_email);
        $this->assertEquals('+15551234567', $employee->mobile);
    }
}
