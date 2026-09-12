<?php

namespace Tests\Feature\Mobility;

use App\Domains\Employee\Models\Employee;
use App\Domains\Mobility\Enums\AssignmentStatus;
use App\Domains\Mobility\Models\MobilityAssignment;
use App\Domains\Mobility\Services\MobilityCostAllocationService;
use App\Domains\Mobility\Services\MobilityCostEstimationService;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class MobilityCostEstimationAndAllocationTest extends TestCase
{
    use RefreshDatabase;

    public function test_cost_items_are_estimated_converted_and_synced_to_budgets(): void
    {
        Carbon::setTestNow('2026-09-01 10:00:00');

        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $homeCompany = Company::factory()->create(['tenant_id' => $tenant->id]);
        $hostCompany = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $homeCompany->id,
            'employee_code' => 'EMP-3001',
            'employee_number' => 'EMP-3001',
            'first_name' => 'Sara',
            'last_name' => 'Connor',
            'official_email' => 'sara@example.com',
            'employment_status' => 'active',
            'joining_date' => '2025-01-01',
        ]);

        $assignment = MobilityAssignment::create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'assignment_number' => 'ASN-TEST-001',
            'home_country' => 'United States',
            'host_country' => 'United Kingdom',
            'home_company_id' => $homeCompany->id,
            'host_company_id' => $hostCompany->id,
            'start_date' => '2026-10-01',
            'planned_end_date' => '2027-09-30',
            'assignment_currency' => 'USD',
            'status' => AssignmentStatus::ACTIVE->value,
        ]);

        $costService = app(MobilityCostEstimationService::class);

        // 1. Add Housing Cost Item (in GBP converted to USD)
        $cost1 = $costService->addCostItem($assignment, [
            'cost_category' => 'housing',
            'cost_name' => 'London Flat Rental',
            'source_amount' => 20000.00,
            'source_currency' => 'GBP',
            'exchange_rate' => 1.300000,
            'converted_currency' => 'USD',
            'frequency' => 'annual',
        ]);

        $this->assertEquals(26000.00, (float) $cost1->converted_amount);

        // Verify budget table sync
        $this->assertDatabaseHas('hcm_mobility_assignment_budgets', [
            'assignment_id' => $assignment->id,
            'budget_category' => 'housing',
            'approved_budget' => 26000.00,
            'actual_amount' => 26000.00,
        ]);

        // 2. Add Relocation Allowance Item
        $cost2 = $costService->addCostItem($assignment, [
            'cost_category' => 'relocation',
            'cost_name' => 'Shipment & Baggage Allowance',
            'source_amount' => 14000.00,
            'source_currency' => 'USD',
            'exchange_rate' => 1.000000,
            'converted_currency' => 'USD',
            'frequency' => 'one_time',
        ]);

        $this->assertEquals(14000.00, (float) $cost2->converted_amount);

        $summary = $costService->getTotalCostSummary($assignment);
        $this->assertEquals(40000.00, (float) $summary['total_amount']);
        $this->assertEquals(26000.00, (float) $summary['by_category']['housing']);
        $this->assertEquals(14000.00, (float) $summary['by_category']['relocation']);

        // 3. Intercompany Cost Allocation
        $allocationService = app(MobilityCostAllocationService::class);

        // Allocating 60% to Host, 40% to Home
        $allocations = $allocationService->allocateCosts($assignment, [
            [
                'entity_role' => 'host',
                'company_id' => $hostCompany->id,
                'allocation_percentage' => 60.00,
                'cost_center_code' => 'CC-UK-RND',
            ],
            [
                'entity_role' => 'home',
                'company_id' => $homeCompany->id,
                'allocation_percentage' => 40.00,
                'cost_center_code' => 'CC-US-HQ',
            ],
        ]);

        $this->assertCount(2, $allocations);
        $this->assertEquals(24000.00, (float) $allocations[0]->allocated_amount); // 60% of 40,000
        $this->assertEquals(16000.00, (float) $allocations[1]->allocated_amount); // 40% of 40,000

        // Verify Finance GL integration staging record was created
        $this->assertDatabaseHas('hcm_mobility_integration_records', [
            'integration_target' => 'finance_gl',
            'entity_type' => 'MobilityCostAllocation',
            'entity_id' => $assignment->id,
            'status' => 'staged',
        ]);
    }

    public function test_allocation_fails_if_percentages_do_not_equal_100(): void
    {
        $tenant = Tenant::factory()->create();
        $homeCompany = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $homeCompany->id,
            'employee_code' => 'EMP-3002',
            'employee_number' => 'EMP-3002',
            'first_name' => 'Alex',
            'last_name' => 'Stone',
            'official_email' => 'alex@example.com',
            'employment_status' => 'active',
            'joining_date' => '2025-01-01',
        ]);

        $assignment = MobilityAssignment::create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'assignment_number' => 'ASN-TEST-002',
            'home_country' => 'United States',
            'host_country' => 'Germany',
            'home_company_id' => $homeCompany->id,
            'host_company_id' => $homeCompany->id,
            'start_date' => '2026-10-01',
            'planned_end_date' => '2027-09-30',
        ]);

        $allocationService = app(MobilityCostAllocationService::class);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Total cost allocation percentage must equal 100%');

        $allocationService->allocateCosts($assignment, [
            [
                'entity_role' => 'home',
                'company_id' => $homeCompany->id,
                'allocation_percentage' => 70.00,
            ],
        ]);
    }
}
