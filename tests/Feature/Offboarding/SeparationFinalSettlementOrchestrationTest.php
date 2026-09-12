<?php

namespace Tests\Feature\Offboarding;

use App\Domains\Employee\Models\Employee;
use App\Domains\Offboarding\Enums\SettlementStatus;
use App\Domains\Offboarding\Models\SeparationRequest;
use App\Domains\Offboarding\Models\SeparationType;
use App\Domains\Offboarding\Services\SeparationFinalSettlementService;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeparationFinalSettlementOrchestrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_final_settlement_orchestration_snapshot_and_audit(): void
    {
        $tenant = Tenant::factory()->create();
        $financeUser = User::factory()->create(['tenant_id' => $tenant->id]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-SET-1',
            'employee_number' => 'EMP-SET-1',
            'first_name' => 'Oscar',
            'last_name' => 'Martinez',
            'official_email' => 'oscar@example.com',
            'joining_date' => now()->toDateString(),
            'employment_status' => 'active',
        ]);

        $type = SeparationType::create([
            'tenant_id' => $tenant->id,
            'code' => 'RESIGNATION',
            'name' => 'Resignation',
        ]);

        $request = SeparationRequest::create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'separation_type_id' => $type->id,
            'request_number' => 'SEP-SET-01',
            'status' => 'clearance',
            'requested_by' => $financeUser->id,
            'requested_at' => now(),
            'proposed_last_working_day' => now()->toDateString(),
            'effective_date' => now()->toDateString(),
        ]);

        $settlementService = new SeparationFinalSettlementService();

        // Record Final Settlement Snapshot
        $settlement = $settlementService->recordSettlementSnapshot($request, [
            'gross_payable' => 12500.00,
            'deductions' => 1500.00,
            'currency' => 'USD',
            'payment_date' => now()->addDays(7)->toDateString(),
            'snapshot_data' => [
                'unpaid_salary' => 9000.00,
                'leave_encashment' => 3500.00,
                'tax_deductions' => 1200.00,
                'loan_recovery' => 300.00,
            ],
        ], $financeUser);

        $this->assertEquals(SettlementStatus::APPROVED->value, $settlement->settlement_status);
        $this->assertEquals(12500.00, (float) $settlement->gross_payable);
        $this->assertEquals(1500.00, (float) $settlement->deductions);
        $this->assertEquals(11000.00, (float) $settlement->net_payable);
        $this->assertNotNull($settlement->approved_at);
        $this->assertEquals(3500.00, $settlement->snapshot_data['leave_encashment']);
    }
}
