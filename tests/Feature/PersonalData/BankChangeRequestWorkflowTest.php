<?php

namespace Tests\Feature\PersonalData;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\PersonalData\Services\BankChangeRequestService;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BankChangeRequestWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_bank_change_request_lifecycle_and_payroll_routing(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $reviewer = User::factory()->create(['tenant_id' => $tenant->id, 'is_platform_admin' => true]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-BANK-01',
            'employee_number' => 'EMP-BANK-01',
            'first_name' => 'George',
            'last_name' => 'Washington',
            'official_email' => 'george@example.com',
            'employment_status' => 'active',
            'joining_date' => now()->toDateString(),
        ]);

        $service = app(BankChangeRequestService::class);
        $rawAccount = '0123456789101112';

        // 1. Submit bank change request
        $request = $service->submitRequest($employee->id, [
            'request_type' => 'update_account',
            'bank_name' => 'Bank of North America',
            'branch_name' => 'Main Financial District',
            'account_title' => 'George Washington',
            'account_number' => $rawAccount,
            'iban' => 'PK36HABB0001234567891011',
            'currency' => 'PKR',
        ]);

        $this->assertEquals('pending', $request->status);
        $this->assertStringStartsWith('BCR-', $request->request_number);
        $this->assertEquals('************1112', $request->masked_account_number);

        // Raw account encrypted at rest
        $rawDb = DB::table('hcm_employee_bank_change_requests')->where('id', $request->id)->first();
        $this->assertNotEquals($rawAccount, $rawDb->account_number_encrypted);

        // 2. Approve request and verify state is applied
        $approved = $service->reviewRequest($request->id, 'approve', null, $reviewer);
        $this->assertEquals('applied_to_payroll', $approved->status);
        $this->assertNotNull($approved->payroll_actioned_at);
        $this->assertEquals($reviewer->id, $approved->reviewed_by);
    }
}
