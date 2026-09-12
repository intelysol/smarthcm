<?php

declare(strict_types=1);

namespace Tests\Feature\HealthSafety;

use App\Domains\HealthSafety\Models\HcmWorkplaceAccommodation;
use App\Domains\HealthSafety\Services\WorkplaceAccommodationService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\BusinessUnit;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkplaceAccommodationTest extends TestCase
{
    use RefreshDatabase;

    public function test_accommodation_request_approval_and_implementation(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $bu = BusinessUnit::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'name' => 'HR BU',
            'code' => 'BU-HR',
            'status' => 'active',
        ]);
        $dept = Department::create([
            'tenant_id' => $tenant->id,
            'business_unit_id' => $bu->id,
            'department_code' => 'DEPT-HR',
            'department_name' => 'HR Operations',
            'status' => 'active',
        ]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'department_id' => $dept->id,
            'employee_code' => 'EMP-ACC-01',
            'employee_number' => 'EMP-ACC-01',
            'first_name' => 'Emma',
            'last_name' => 'Watson',
            'official_email' => 'emma.w@example.com',
            'employment_status' => 'active',
            'joining_date' => now()->toDateString(),
        ]);

        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $service = app(WorkplaceAccommodationService::class);

        // 1. Request
        $acc = $service->requestAccommodation([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_id' => $employee->id,
            'accommodation_type' => 'ergonomic_equipment',
            'title' => 'Electric Sit-Stand Desk & Ergonomic Chair',
            'description' => 'Required to alternate posture every 30 minutes due to chronic lower back strain.',
            'cost_estimate' => 850.00,
        ], $user->id);

        $this->assertEquals('requested', $acc->status);

        // 2. Approve
        $approved = $service->assessAccommodation($acc, true, [
            'notes' => 'Approved under workplace health inclusion budget.',
            'cost_actual' => 790.00,
        ], $user->id);

        $this->assertEquals('approved', $approved->status);

        // 3. Implement
        $implemented = $service->implementAccommodation($approved, [
            'start_date' => now()->toDateString(),
        ], $user->id);

        $this->assertEquals('implemented', $implemented->status);
    }
}
