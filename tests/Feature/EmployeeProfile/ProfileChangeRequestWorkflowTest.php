<?php

namespace Tests\Feature\EmployeeProfile;

use App\Domains\Employee\Models\Employee;
use App\Domains\EmployeeProfile\Services\ProfileChangeRequestService;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ProfileChangeRequestWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_change_request_approval_and_rejection(): void
    {
        $tenant = Tenant::factory()->create();
        $hrUser = User::factory()->create(['tenant_id' => $tenant->id, 'is_platform_admin' => true]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-REQ-10',
            'employee_number' => 'EMP-REQ-10',
            'first_name' => 'Peter',
            'last_name' => 'Parker',
            'mobile' => '111-222-3333',
            'present_address' => 'Forest Hills, Queens',
            'employment_status' => 'active',
            'joining_date' => now()->toDateString(),
        ]);

        $service = new ProfileChangeRequestService();

        // 1. Disallow modification of authoritative field (e.g. department_id)
        try {
            $service->createChangeRequest($employee, ['department_id' => 'unauthorized-dept']);
            $this->fail('Expected ValidationException when attempting to change authoritative field');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('field', $e->errors());
        }

        // 2. Submit valid personal change request
        $request = $service->createChangeRequest($employee, [
            'mobile' => '999-888-7777',
            'present_address' => 'New York City, Manhattan',
        ], 'Relocated closer to office');

        $this->assertEquals('pending', $request->status);
        $this->assertCount(2, $request->items);

        // 3. Approve Request -> updates Core HR Employee record
        $approved = $service->approveRequest($request, $hrUser);
        $this->assertEquals('approved', $approved->status);
        $this->assertEquals($hrUser->id, $approved->reviewed_by);

        // Verify Core HR Employee model was updated
        $this->assertEquals('999-888-7777', $employee->fresh()->mobile);
        $this->assertEquals('New York City, Manhattan', $employee->fresh()->present_address);

        // 4. Reject Request requires mandatory reason
        $request2 = $service->createChangeRequest($employee, ['emergency_phone' => '000-111-2222']);
        try {
            $service->rejectRequest($request2, $hrUser, '');
            $this->fail('Expected ValidationException when rejecting without reason');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('reason', $e->errors());
        }

        $rejected = $service->rejectRequest($request2, $hrUser, 'Invalid emergency contact telephone number');
        $this->assertEquals('rejected', $rejected->status);
        $this->assertEquals('Invalid emergency contact telephone number', $rejected->rejection_reason);
    }
}
