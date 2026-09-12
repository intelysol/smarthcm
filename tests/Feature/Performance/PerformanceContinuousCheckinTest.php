<?php

declare(strict_types=1);

namespace Tests\Feature\Performance;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Performance\Models\PerformanceCycle;
use App\Domains\Performance\Services\PerformanceCheckinService;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Database\Seeders\PerformancePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerformanceContinuousCheckinTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PerformancePermissionSeeder::class);
    }

    public function test_schedule_and_complete_continuous_checkin(): void
    {
        $tenant = Tenant::factory()->create();
        app(\App\Domains\Platform\Contracts\TenantContext::class)->set($tenant);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-CHK-01',
            'employee_number' => 'EMP-CHK-01',
            'first_name' => 'Sara',
            'last_name' => 'Connor',
            'official_email' => 'sara@example.com',
            'employment_status' => 'active',
            'joining_date' => now()->toDateString(),
        ]);

        $cycle = PerformanceCycle::create([
            'tenant_id' => $tenant->id,
            'name' => '2026 Continuous Cycle',
            'cycle_type' => 'continuous',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'status' => 'open',
        ]);

        $service = app(PerformanceCheckinService::class);

        // 1. Schedule Checkin
        $checkin = $service->scheduleCheckin([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'employee_id' => $employee->id,
            'scheduled_at' => now()->addDays(3),
            'agenda_items' => ['Review sprint milestones', 'Discuss platform dependency blockers'],
        ], $user);

        $this->assertEquals('scheduled', $checkin->status);
        $this->assertCount(2, $checkin->agenda_items);

        // 2. Complete Checkin
        $completed = $service->completeCheckin($checkin, [
            'summary' => 'Productive conversation, backend API blocker resolved with DevOps team.',
            'employee_comment' => 'Feeling aligned with upcoming Q3 deadlines.',
            'manager_comment' => 'Great technical velocity this sprint.',
        ], $user);

        $this->assertEquals('completed', $completed->status);
        $this->assertNotNull($completed->completed_at);
        $this->assertEquals('Productive conversation, backend API blocker resolved with DevOps team.', $completed->summary);
    }
}
