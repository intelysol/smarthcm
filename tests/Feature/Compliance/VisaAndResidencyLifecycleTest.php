<?php

namespace Tests\Feature\Compliance;

use App\Domains\Compliance\Services\VisaService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisaAndResidencyLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_records_and_updates_visa_record(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-VISA-01',
            'employee_number' => 'EMP-VISA-01',
            'first_name' => 'Akira',
            'last_name' => 'Tetsuo',
            'official_email' => 'akira@example.com',
            'employment_status' => 'active',
            'joining_date' => now()->toDateString(),
        ]);

        $service = app(VisaService::class);

        $visa = $service->addVisa((string) $employee->id, [
            'visa_type' => 'H-1B',
            'visa_number' => 'V-99881122',
            'issuing_country' => 'USA',
            'issue_date' => now()->subYear()->toDateString(),
            'effective_from' => now()->subYear()->toDateString(),
            'expiry_date' => now()->addYears(2)->toDateString(),
            'is_multiple_entry' => true,
            'sponsor' => 'Enterprise HCM Tech Inc.',
        ], $user);

        $this->assertDatabaseHas('hcm_employee_visa_records', [
            'id' => $visa->id,
            'visa_number' => 'V-99881122',
            'visa_type' => 'H-1B',
            'status' => 'active',
        ]);

        $updated = $service->updateVisa((string) $visa->id, [
            'notes' => 'Limited to North America travel',
        ], $user);

        $this->assertEquals('Limited to North America travel', $updated->notes);
    }
}
