<?php

namespace Tests\Feature\Mobility;

use App\Domains\Employee\Models\Employee;
use App\Domains\Mobility\Enums\AssignmentStatus;
use App\Domains\Mobility\Models\MobilityAssignment;
use App\Domains\Mobility\Services\MobilityAssignmentService;
use App\Domains\Mobility\Services\MobilityChangeService;
use App\Domains\Mobility\Services\MobilityExtensionService;
use App\Domains\Mobility\Services\MobilityRelocationService;
use App\Domains\Mobility\Services\MobilityTaskService;
use App\Domains\Mobility\Services\RepatriationService;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobilityRelocationAndLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_relocation_case_and_checklist_workflow(): void
    {
        Carbon::setTestNow('2026-09-01 10:00:00');

        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-4001',
            'employee_number' => 'EMP-4001',
            'first_name' => 'David',
            'last_name' => 'Miller',
            'official_email' => 'david@example.com',
            'employment_status' => 'active',
            'joining_date' => '2025-01-01',
        ]);

        $assignment = MobilityAssignment::create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'assignment_number' => 'ASN-TEST-003',
            'home_country' => 'United States',
            'host_country' => 'Singapore',
            'home_company_id' => $company->id,
            'host_company_id' => $company->id,
            'start_date' => '2026-10-01',
            'planned_end_date' => '2027-09-30',
            'status' => AssignmentStatus::PLANNING->value,
        ]);

        $relocationService = app(MobilityRelocationService::class);

        // 1. Initiate Relocation with family
        $case = $relocationService->initiateRelocationCase($assignment, [
            'relocation_provider_name' => 'Global Relo Logistics Ltd',
            'provider_reference' => 'GR-9923',
            'family_relocating' => true,
        ], $user);

        $this->assertDatabaseHas('hcm_mobility_relocation_cases', [
            'id' => $case->id,
            'assignment_id' => $assignment->id,
            'family_relocating' => true,
        ]);

        // Standard items + schooling item seeded
        $this->assertGreaterThanOrEqual(7, $case->items()->count());

        // 2. Complete all items
        foreach ($case->items as $item) {
            $relocationService->completeItem($item);
        }

        $this->assertEquals('completed', $case->fresh()->status);
        $this->assertNotNull($case->fresh()->actual_move_date);
    }

    public function test_assignment_lifecycle_tasks_extension_change_and_repatriation(): void
    {
        Carbon::setTestNow('2026-09-01 10:00:00');

        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-4002',
            'employee_number' => 'EMP-4002',
            'first_name' => 'Emma',
            'last_name' => 'Watson',
            'official_email' => 'emma@example.com',
            'employment_status' => 'active',
            'joining_date' => '2025-01-01',
        ]);

        $assignmentService = app(MobilityAssignmentService::class);

        $assignment = MobilityAssignment::create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'assignment_number' => 'ASN-TEST-004',
            'home_country' => 'United States',
            'host_country' => 'Japan',
            'home_company_id' => $company->id,
            'host_company_id' => $company->id,
            'start_date' => '2026-10-01',
            'planned_end_date' => '2027-09-30',
            'current_version' => 1,
            'status' => AssignmentStatus::ACTIVE->value,
        ]);

        // 1. Generate Lifecycle Tasks
        $taskService = app(MobilityTaskService::class);
        $tasks = $taskService->generateLifecycleTasks($assignment);
        $this->assertCount(9, $tasks);

        $taskService->completeTask($tasks[0], $user);
        $this->assertEquals('completed', $tasks[0]->fresh()->status);

        Carbon::setTestNow('2026-09-01 11:00:00');

        // 2. Assignment Extension
        $extensionService = app(MobilityExtensionService::class);
        $extension = $extensionService->requestExtension($assignment, [
            'proposed_end_date' => '2028-03-31',
            'extension_reason' => 'Client requested extension of phase 2 project milestone.',
            'additional_estimated_cost' => 35000.00,
        ], $user);

        $this->assertEquals('requested', $extension->status);

        Carbon::setTestNow('2026-09-01 12:00:00');

        $extensionService->approveExtension($extension, $user);
        $this->assertEquals('approved', $extension->fresh()->status);
        $this->assertEquals('2028-03-31', $assignment->fresh()->planned_end_date->toDateString());
        $this->assertEquals(2, $assignment->fresh()->current_version);

        Carbon::setTestNow('2026-09-01 13:00:00');

        // 3. In-flight Assignment Change
        $changeService = app(MobilityChangeService::class);
        $change = $changeService->requestChange($assignment, [
            'change_type' => 'cost_center',
            'previous_values' => ['cost_center_code' => 'CC-OLD'],
            'proposed_values' => ['cost_center_code' => 'CC-NEW-77'],
            'reason' => 'Reorganization of Asia Pacific business unit.',
        ], $user);

        Carbon::setTestNow('2026-09-01 14:00:00');

        $changeService->approveChange($change, $user);
        $this->assertEquals('executed', $change->fresh()->status);
        $this->assertEquals('CC-NEW-77', $assignment->fresh()->cost_center_code);
        $this->assertEquals(3, $assignment->fresh()->current_version);

        Carbon::setTestNow('2026-09-01 15:00:00');

        // 4. Repatriation Workflow
        $repatriationService = app(RepatriationService::class);
        $repatriation = $repatriationService->initiateRepatriation($assignment, [
            'planned_return_date' => '2028-03-31',
            'outcome_type' => 'return_to_original',
        ], $user);

        $this->assertEquals(AssignmentStatus::REPATRIATING, $assignment->fresh()->status);
        $this->assertDatabaseHas('hcm_mobility_repatriations', [
            'id' => $repatriation->id,
            'status' => 'planning',
        ]);

        Carbon::setTestNow('2026-09-01 16:00:00');

        // Complete Repatriation
        $repatriationService->completeRepatriation($repatriation, $user);
        $this->assertEquals('completed', $repatriation->fresh()->status);
        $this->assertTrue($repatriation->fresh()->expense_settlement_completed);
        $this->assertEquals(AssignmentStatus::COMPLETED, $assignment->fresh()->status);
        $this->assertTrue($assignment->fresh()->is_repatriated);
    }
}
