<?php

namespace Tests\Feature\Offboarding;

use App\Domains\Employee\Models\Employee;
use App\Domains\Offboarding\Enums\ClearanceStatus;
use App\Domains\Offboarding\Models\SeparationRequest;
use App\Domains\Offboarding\Models\SeparationType;
use App\Domains\Offboarding\Services\SeparationClearanceService;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeparationClearanceMatrixAndWaiverTest extends TestCase
{
    use RefreshDatabase;

    public function test_clearance_matrix_initialization_item_clearance_and_department_waiver(): void
    {
        $tenant = Tenant::factory()->create();
        $adminUser = User::factory()->create(['tenant_id' => $tenant->id, 'is_platform_admin' => true]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-CLR-1',
            'employee_number' => 'EMP-CLR-1',
            'first_name' => 'Stanley',
            'last_name' => 'Hudson',
            'official_email' => 'stanley@example.com',
            'joining_date' => now()->toDateString(),
            'employment_status' => 'active',
        ]);

        $type = SeparationType::create([
            'tenant_id' => $tenant->id,
            'code' => 'RETIREMENT',
            'name' => 'Retirement',
            'requires_clearance' => true,
        ]);

        $request = SeparationRequest::create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'separation_type_id' => $type->id,
            'request_number' => 'SEP-CLR-01',
            'status' => 'approved',
            'requested_by' => $adminUser->id,
            'requested_at' => now(),
            'proposed_last_working_day' => now()->addDays(60)->toDateString(),
            'effective_date' => now()->addDays(60)->toDateString(),
        ]);

        $clearanceService = new SeparationClearanceService();

        // 1. Initialize Clearance Matrix
        $clearanceService->initializeClearance($request);
        $this->assertCount(4, $request->clearances);

        // 2. Clear individual items in HR clearance
        $hrClearance = $request->clearances()->where('department', 'hr')->first();
        $this->assertEquals(ClearanceStatus::PENDING->value, $hrClearance->status);

        foreach ($hrClearance->items as $item) {
            $clearanceService->clearItem($item, $adminUser, 'Item verified and signed off');
        }
        $this->assertEquals(ClearanceStatus::CLEARED->value, $hrClearance->fresh()->status);
        $this->assertNotNull($hrClearance->fresh()->cleared_at);

        // 3. Waive IT Clearance with authorization and rationale
        $itClearance = $request->clearances()->where('department', 'it')->first();
        $waived = $clearanceService->waiveClearance($itClearance, $adminUser, 'Employee had no company-owned IT assets assigned');
        $this->assertEquals(ClearanceStatus::WAIVED->value, $waived->status);
        $this->assertNotNull($waived->waived_by);
        $this->assertEquals(ClearanceStatus::WAIVED->value, $itClearance->items->first()->status);
    }
}
