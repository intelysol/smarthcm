<?php

declare(strict_types=1);

namespace Tests\Feature\HealthSafety;

use App\Domains\HealthSafety\Models\HcmReturnToWorkCase;
use App\Domains\HealthSafety\Services\ReturnToWorkService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\BusinessUnit;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReturnToWorkTest extends TestCase
{
    use RefreshDatabase;

    public function test_return_to_work_initiation_plan_and_completion(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $bu = BusinessUnit::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'name' => 'Ops BU',
            'code' => 'BU-OPS',
            'status' => 'active',
        ]);
        $dept = Department::create([
            'tenant_id' => $tenant->id,
            'business_unit_id' => $bu->id,
            'department_code' => 'DEPT-OPS',
            'department_name' => 'Operations Dept',
            'status' => 'active',
        ]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'department_id' => $dept->id,
            'employee_code' => 'EMP-RTW-01',
            'employee_number' => 'EMP-RTW-01',
            'first_name' => 'Michael',
            'last_name' => 'Chang',
            'official_email' => 'm.chang@example.com',
            'employment_status' => 'active',
            'joining_date' => now()->toDateString(),
        ]);

        $service = app(ReturnToWorkService::class);

        // 1. Initiate case
        $case = $service->initiateCase([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_id' => $employee->id,
            'leave_start_date' => now()->subDays(14)->toDateString(),
            'work_capacity_status' => 'partial_capacity',
        ]);

        $this->assertEquals('open', $case->status);

        // 2. Establish phased plan
        $planned = $service->createPlan($case, [
            'target_return_date' => now()->addDays(30)->toDateString(),
            'phased_plan' => [
                ['week' => 1, 'hours_per_week' => 20, 'duties' => 'Desk administration only'],
                ['week' => 2, 'hours_per_week' => 30, 'duties' => 'Light supervision'],
                ['week' => 3, 'hours_per_week' => 40, 'duties' => 'Full return with no lifting'],
            ],
        ]);

        $this->assertEquals('plan_active', $planned->status);

        // 3. Complete case
        $completed = $service->completeCase($planned, [
            'actual_return_date' => now()->toDateString(),
            'notes' => 'Successfully resumed normal duties without recurring symptoms.',
        ]);

        $this->assertEquals('completed', $completed->status);
        $this->assertNotNull($completed->actual_return_date);
    }
}
