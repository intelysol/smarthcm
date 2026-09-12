<?php

declare(strict_types=1);

namespace Tests\Feature\Performance;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Performance\Models\PerformanceCycle;
use App\Domains\Performance\Services\PerformanceImprovementPlanService;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Database\Seeders\PerformancePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerformanceImprovementPlanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PerformancePermissionSeeder::class);
    }

    public function test_pip_lifecycle_creation_and_successful_completion(): void
    {
        $tenant = Tenant::factory()->create();
        app(\App\Domains\Platform\Contracts\TenantContext::class)->set($tenant);
        $manager = User::factory()->create(['tenant_id' => $tenant->id]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-PIP-01',
            'employee_number' => 'EMP-PIP-01',
            'first_name' => 'Bilal',
            'last_name' => 'Hassan',
            'official_email' => 'bilal@example.com',
            'employment_status' => 'active',
            'joining_date' => now()->toDateString(),
        ]);

        $cycle = PerformanceCycle::create([
            'tenant_id' => $tenant->id,
            'name' => '2026 Appraisal Cycle',
            'cycle_type' => 'annual',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'status' => 'in_progress',
        ]);

        $service = app(PerformanceImprovementPlanService::class);

        // 1. Create PIP with 2 Action Milestones
        $pip = $service->createPip([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'cycle_id' => $cycle->id,
            'summary' => '60-day performance improvement plan focusing on unit test coverage and production incident response.',
            'actions' => [
                ['title' => 'Increase unit test coverage on payment service to 85%', 'due_date' => now()->addDays(30)->toDateString()],
                ['title' => 'Complete incident management rotation with zero missed alerts', 'due_date' => now()->addDays(60)->toDateString()],
            ],
        ], $manager);

        $this->assertEquals('active', $pip->status);
        $this->assertCount(2, $pip->actions);

        // 2. Conclude PIP with Successful Completion
        $concluded = $service->concludePip($pip, 'successful', 'Employee met all target coverage milestones and successfully concluded PIP.');
        $this->assertEquals('successful', $concluded->status);
        $this->assertEquals('Employee met all target coverage milestones and successfully concluded PIP.', $concluded->details['conclusion_notes']);
    }
}
