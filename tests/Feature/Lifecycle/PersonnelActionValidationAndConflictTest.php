<?php

namespace Tests\Feature\Lifecycle;

use App\Domains\Employee\Models\Employee;
use App\Domains\Lifecycle\Enums\PersonnelActionStatus;
use App\Domains\Lifecycle\Models\PersonnelActionChange;
use App\Domains\Lifecycle\Models\PersonnelActionRequest;
use App\Domains\Lifecycle\Models\PersonnelActionType;
use App\Domains\Lifecycle\Services\PersonnelActionConflictService;
use App\Domains\Lifecycle\Services\PersonnelActionValidationService;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Position;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PersonnelActionValidationAndConflictTest extends TestCase
{
    use RefreshDatabase;

    public function test_conflict_detection_and_blocking_validation(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-CONF-1',
            'employee_number' => 'EMP-CONF-1',
            'first_name' => 'Sara',
            'last_name' => 'Connor',
            'official_email' => 'sara@example.com',
            'joining_date' => now()->toDateString(),
            'employment_status' => 'active',
        ]);

        $position = Position::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'title' => 'Chief Technology Officer',
            'code' => 'POS-CTO-01',
            'is_active' => true,
        ]);

        $actionType = PersonnelActionType::create([
            'tenant_id' => $tenant->id,
            'code' => 'PROMOTION',
            'name' => 'Promotion',
        ]);

        $targetDate = now()->addDays(15)->toDateString();

        // Existing scheduled action on target date
        $existingAction = PersonnelActionRequest::create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'action_type_id' => $actionType->id,
            'request_number' => 'PA-EXISTING-01',
            'status' => PersonnelActionStatus::PENDING_APPROVAL->value,
            'requested_by' => $user->id,
            'requested_at' => now(),
            'effective_date' => $targetDate,
        ]);

        // Second action for same employee on same date
        $newAction = PersonnelActionRequest::create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'action_type_id' => $actionType->id,
            'request_number' => 'PA-NEW-02',
            'status' => PersonnelActionStatus::DRAFT->value,
            'requested_by' => $user->id,
            'requested_at' => now(),
            'effective_date' => $targetDate,
        ]);

        $validator = new PersonnelActionValidationService();

        // 1. Conflict detection flags overlapping action
        try {
            $validator->validateSubmission($newAction);
            $this->fail('Expected ValidationException due to overlapping action conflict');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('conflict', $e->errors());
            $this->assertStringContainsString('Another active action', $e->errors()['conflict'][0]);
        }
    }
}
