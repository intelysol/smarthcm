<?php

namespace Tests\Feature\PersonalData;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\PersonalData\Services\EmergencyContactService;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmergencyContactPriorityTest extends TestCase
{
    use RefreshDatabase;

    public function test_emergency_contact_priority_and_primary_promotion(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-EMERG-01',
            'employee_number' => 'EMP-EMERG-01',
            'first_name' => 'Benjamin',
            'last_name' => 'Franklin',
            'official_email' => 'ben@example.com',
            'employment_status' => 'active',
            'joining_date' => now()->toDateString(),
        ]);

        $service = app(EmergencyContactService::class);

        // 1. Add first contact (should become primary automatically)
        $c1 = $service->addContact($employee->id, [
            'name' => 'Deborah Read',
            'relationship' => 'Spouse',
            'primary_phone' => '+15551112222',
            'priority_order' => 1,
        ]);

        $this->assertTrue($c1->is_primary);
        $employee->refresh();
        $this->assertEquals('+15551112222', $employee->emergency_phone);

        // 2. Add second contact as primary
        $c2 = $service->addContact($employee->id, [
            'name' => 'William Franklin',
            'relationship' => 'Son',
            'primary_phone' => '+15553334444',
            'priority_order' => 2,
            'is_primary' => true,
        ]);

        $this->assertTrue($c2->is_primary);
        $c1->refresh();
        $this->assertFalse($c1->is_primary);

        $employee->refresh();
        $this->assertEquals('+15553334444', $employee->emergency_phone);

        // 3. Delete current primary (c2) -> c1 should be promoted to primary
        $service->deleteContact($c2->id);

        $c1->refresh();
        $this->assertTrue($c1->is_primary);

        $employee->refresh();
        $this->assertEquals('+15551112222', $employee->emergency_phone);
    }
}
