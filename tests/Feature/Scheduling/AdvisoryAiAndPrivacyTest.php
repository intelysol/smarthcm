<?php

namespace Tests\Feature\Scheduling;

use App\Domains\Attendance\Models\HcmEmployeeAvailability;
use App\Domains\Attendance\Models\HcmScheduleCoverageRequirement;
use App\Domains\Attendance\Models\RosterPeriod;
use App\Domains\Attendance\Models\ShiftDefinition;
use App\Domains\Attendance\Services\Scheduling\AdvisorySchedulingAiService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use Database\Seeders\AttendancePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdvisoryAiAndPrivacyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AttendancePermissionSeeder::class);
    }

    public function test_advisory_ai_output_and_explainability_boundaries(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $period = RosterPeriod::query()->create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'name' => 'AI Test Period',
            'start_date' => '2026-10-05',
            'end_date' => '2026-10-07',
            'status' => 'draft',
        ]);

        $shift = ShiftDefinition::query()->create([
            'tenant_id' => $tenant->id,
            'shift_code' => 'NIGHT',
            'name' => 'Night Shift',
            'start_time' => '00:00',
            'end_time' => '08:00',
            'duration_minutes' => 480,
            'is_active' => true,
        ]);

        HcmScheduleCoverageRequirement::query()->create([
            'tenant_id' => $tenant->id,
            'roster_period_id' => $period->id,
            'requirement_date' => '2026-10-05',
            'shift_definition_id' => $shift->id,
            'required_headcount' => 3,
        ]);

        $aiService = app(AdvisorySchedulingAiService::class);
        $insights = $aiService->generateAdvisoryInsights($period);

        // Strict AI safety assertions
        $this->assertTrue($insights['is_advisory_only'], 'AI suggestions must always be advisory only.');
        $this->assertTrue($insights['human_review_required'], 'AI suggestions must mandate human review.');
        $this->assertNotEmpty($insights['recommendations']);

        $rec = $insights['recommendations'][0];
        $this->assertArrayHasKey('trade_offs', $rec);
        $this->assertArrayHasKey('confidence', $rec);
        $this->assertGreaterThan(0.5, $rec['confidence']);
    }

    public function test_employee_privacy_availability_reason_protection(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $emp = $this->createEmployee($tenant->id, $company->id);

        $avail = HcmEmployeeAvailability::query()->create([
            'tenant_id' => $tenant->id,
            'employee_id' => $emp->id,
            'date' => '2026-10-05',
            'availability_type' => 'unavailable',
            'reason' => 'Private medical appointment - confidential',
        ]);

        $this->assertDatabaseHas('hcm_employee_availabilities', [
            'id' => $avail->id,
            'availability_type' => 'unavailable',
        ]);

        // When retrieved, reason remains stored but is guarded from peer roster views
        $retrieved = HcmEmployeeAvailability::query()->find($avail->id);
        $this->assertEquals('Private medical appointment - confidential', $retrieved->reason);
    }

    protected function createEmployee(string $tenantId, string $companyId, array $attributes = []): Employee
    {
        return Employee::query()->create(array_merge([
            'tenant_id' => $tenantId,
            'company_id' => $companyId,
            'employee_code' => 'EMP-' . uniqid(),
            'employee_number' => 'EMP-' . rand(1000, 9999),
            'first_name' => 'Privacy',
            'last_name' => 'User',
            'official_email' => 'privacy.' . uniqid() . '@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ], $attributes));
    }
}
