<?php

namespace Tests\Feature\PersonalData;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\PersonalData\Services\PersonalDataChangeRequestService;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonalDataChangeRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_change_request_side_by_side_and_approval(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $reviewer = User::factory()->create(['tenant_id' => $tenant->id, 'is_platform_admin' => true]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-DCR-01',
            'employee_number' => 'EMP-DCR-01',
            'first_name' => 'James',
            'last_name' => 'Monroe',
            'marital_status' => 'single',
            'official_email' => 'james.monroe@example.com',
            'employment_status' => 'active',
            'joining_date' => now()->toDateString(),
        ]);

        $service = app(PersonalDataChangeRequestService::class);

        // 1. Submit personal change request
        $request = $service->submitChangeRequest(
            $employee->id,
            'personal',
            [
                [
                    'target_entity' => 'personal_data',
                    'field_name' => 'marital_status',
                    'old_value' => 'single',
                    'new_value' => 'married',
                ],
                [
                    'target_entity' => 'personal_data',
                    'field_name' => 'preferred_name',
                    'old_value' => null,
                    'new_value' => 'Jim',
                ],
            ],
            [
                'reason' => 'Recent marriage update',
            ]
        );

        $this->assertEquals('pending', $request->status);
        $this->assertCount(2, $request->items);

        // 2. Approve change request
        $approved = $service->reviewChangeRequest($request->id, 'approve', null, $reviewer);
        $this->assertEquals('applied', $approved->status);

        // Verify changes applied to Core HR employee
        $employee->refresh();
        $this->assertEquals('married', $employee->marital_status);
        $this->assertEquals('Jim', $employee->preferred_name);
    }
}
