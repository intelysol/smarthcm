<?php

namespace Tests\Feature\PersonalData;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\PersonalData\Services\DependentService;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DependentProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_dependent_profile_management(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-DEP-01',
            'employee_number' => 'EMP-DEP-01',
            'first_name' => 'John',
            'last_name' => 'Adams',
            'official_email' => 'john.adams@example.com',
            'employment_status' => 'active',
            'joining_date' => now()->toDateString(),
        ]);

        $service = app(DependentService::class);

        // 1. Add spouse
        $spouse = $service->addDependent($employee->id, [
            'first_name' => 'Abigail',
            'last_name' => 'Adams',
            'relationship' => 'spouse',
            'date_of_birth' => '1980-11-22',
            'gender' => 'female',
            'coverage_eligible' => true,
        ]);

        $this->assertEquals('Abigail', $spouse->first_name);
        $this->assertEquals('spouse', $spouse->relationship);

        // 2. Add child
        $child = $service->addDependent($employee->id, [
            'first_name' => 'John Quincy',
            'last_name' => 'Adams',
            'relationship' => 'child',
            'date_of_birth' => '2010-07-11',
            'gender' => 'male',
            'is_student' => true,
            'coverage_eligible' => true,
        ]);

        $this->assertEquals('John Quincy', $child->first_name);
        $this->assertTrue($child->is_student);

        // 3. List dependents
        $dependents = $service->getDependents($employee->id);
        $this->assertCount(2, $dependents);

        // 4. Delete dependent
        $service->deleteDependent($child->id);
        $this->assertCount(1, $service->getDependents($employee->id));
    }
}
