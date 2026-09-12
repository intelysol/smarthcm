<?php

namespace Tests\Feature\Lifecycle;

use App\Domains\Employee\Models\Employee;
use App\Domains\Lifecycle\Enums\PersonnelActionStatus;
use App\Domains\Lifecycle\Jobs\ProcessEffectivePersonnelActionsJob;
use App\Domains\Lifecycle\Models\PersonnelActionChange;
use App\Domains\Lifecycle\Models\PersonnelActionRequest;
use App\Domains\Lifecycle\Models\PersonnelActionType;
use App\Domains\Lifecycle\Services\PersonnelActionExecutionService;
use App\Domains\Lifecycle\Services\PersonnelActionService;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PersonnelActionFutureAndBackdatedTest extends TestCase
{
    use RefreshDatabase;

    public function test_future_scheduled_action_and_backdated_authorization(): void
    {
        $tenant = Tenant::factory()->create();
        $adminUser = User::factory()->create(['tenant_id' => $tenant->id, 'is_platform_admin' => true]);
        $regularUser = User::factory()->create(['tenant_id' => $tenant->id, 'is_platform_admin' => false]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-FUT-1',
            'employee_number' => 'EMP-FUT-1',
            'first_name' => 'Michael',
            'last_name' => 'Scott',
            'official_email' => 'michael@example.com',
            'joining_date' => now()->toDateString(),
            'employment_status' => 'active',
        ]);

        $actionType = PersonnelActionType::create([
            'tenant_id' => $tenant->id,
            'code' => 'PROMOTION',
            'name' => 'Promotion',
        ]);

        $service = new PersonnelActionService();

        // 1. Backdated validation: Regular user cannot create backdated request
        try {
            $service->createRequest($regularUser, [
                'employee_id' => $employee->id,
                'action_type_id' => $actionType->id,
                'effective_date' => now()->subDays(30)->toDateString(),
                'changes' => [
                    ['field_name' => 'employment_status', 'old_value' => 'active', 'new_value' => 'active'],
                ],
            ]);
            $this->fail('Expected ValidationException for unauthorized backdated action');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('effective_date', $e->errors());
        }

        // 2. Future-dated action: Approval puts it into SCHEDULED status
        $futureDate = now()->addDays(5)->toDateString();
        $futureRequest = $service->createRequest($adminUser, [
            'employee_id' => $employee->id,
            'action_type_id' => $actionType->id,
            'effective_date' => $futureDate,
            'changes' => [
                ['field_name' => 'employment_status', 'old_value' => 'active', 'new_value' => 'active'],
            ],
        ]);
        $submitted = $service->submitRequest($futureRequest, $adminUser);
        $approved = $service->approveRequest($submitted, $adminUser);
        $this->assertEquals(PersonnelActionStatus::SCHEDULED->value, $approved->status);

        // 3. ProcessEffectivePersonnelActionsJob: Simulate arrival of effective date
        $futureRequest->update(['effective_date' => now()->toDateString()]);
        $job = new ProcessEffectivePersonnelActionsJob();
        $job->handle(new PersonnelActionExecutionService());

        $this->assertEquals(PersonnelActionStatus::EXECUTED->value, $futureRequest->fresh()->status);
    }
}
