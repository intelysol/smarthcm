<?php

namespace Tests\Feature\PersonalData;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\PersonalData\Services\AddressService;
use App\Domains\Shared\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddressEffectiveDatingTest extends TestCase
{
    use RefreshDatabase;

    public function test_address_effective_dating_and_single_current_rule(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-ADDR-01',
            'employee_number' => 'EMP-ADDR-01',
            'first_name' => 'Thomas',
            'last_name' => 'Jefferson',
            'official_email' => 'thomas@example.com',
            'employment_status' => 'active',
            'joining_date' => now()->toDateString(),
        ]);

        $service = app(AddressService::class);

        // 1. Add initial home address
        $address1 = $service->addAddress($employee->id, [
            'address_type' => 'home',
            'address_line_1' => '100 Monticello Way',
            'city' => 'Charlottesville',
            'state_province' => 'Virginia',
            'postal_code' => '22902',
            'country' => 'USA',
            'is_current' => true,
            'effective_from' => '2023-01-01',
        ]);

        $this->assertTrue($address1->is_current);

        $employee->refresh();
        $this->assertStringContainsString('100 Monticello Way', $employee->present_address);
        $this->assertEquals('Charlottesville', $employee->city);

        // 2. Move to new home address effective today
        $address2 = $service->addAddress($employee->id, [
            'address_type' => 'home',
            'address_line_1' => '1600 Pennsylvania Ave',
            'city' => 'Washington',
            'state_province' => 'DC',
            'postal_code' => '20500',
            'country' => 'USA',
            'is_current' => true,
            'effective_from' => Carbon::today()->toDateString(),
        ]);

        $this->assertTrue($address2->is_current);

        // Check address1 is deactivated and effective_to closed
        $address1->refresh();
        $this->assertFalse($address1->is_current);
        $this->assertEquals(Carbon::yesterday()->toDateString(), $address1->effective_to->toDateString());

        // Check employee present address was updated
        $employee->refresh();
        $this->assertStringContainsString('1600 Pennsylvania Ave', $employee->present_address);
        $this->assertEquals('Washington', $employee->city);
    }
}
