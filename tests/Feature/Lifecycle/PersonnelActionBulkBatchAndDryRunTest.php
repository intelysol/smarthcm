<?php

namespace Tests\Feature\Lifecycle;

use App\Domains\Employee\Models\Employee;
use App\Domains\Lifecycle\Enums\BulkBatchStatus;
use App\Domains\Lifecycle\Models\PersonnelActionType;
use App\Domains\Lifecycle\Services\PersonnelActionBulkService;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonnelActionBulkBatchAndDryRunTest extends TestCase
{
    use RefreshDatabase;

    public function test_bulk_batch_dry_run_validation_and_execution(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $activeEmp = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-BLK-1',
            'employee_number' => 'EMP-BLK-1',
            'first_name' => 'Angela',
            'last_name' => 'Martin',
            'official_email' => 'angela@example.com',
            'joining_date' => now()->toDateString(),
            'employment_status' => 'active',
        ]);

        $termEmp = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-BLK-2',
            'employee_number' => 'EMP-BLK-2',
            'first_name' => 'Roy',
            'last_name' => 'Anderson',
            'official_email' => 'roy@example.com',
            'joining_date' => now()->toDateString(),
            'employment_status' => 'terminated',
        ]);

        $actionType = PersonnelActionType::create([
            'tenant_id' => $tenant->id,
            'code' => 'COMPENSATION_CHANGE',
            'name' => 'Annual Merit Increment',
        ]);

        $bulkService = new PersonnelActionBulkService();

        // 1. Create Batch
        $batch = $bulkService->createBatch($user, $actionType, 'Q4 Merit Increase', [
            [
                'employee_id' => $activeEmp->id,
                'payload' => [
                    'changes' => [
                        ['field_name' => 'base_salary', 'old_value' => '100000', 'new_value' => '105000'],
                    ],
                ],
            ],
            [
                'employee_id' => $termEmp->id,
                'payload' => [
                    'changes' => [
                        ['field_name' => 'base_salary', 'old_value' => '90000', 'new_value' => '95000'],
                    ],
                ],
            ],
        ]);

        $this->assertEquals(2, $batch->total_items);
        $this->assertEquals(BulkBatchStatus::DRAFT->value, $batch->status);

        // 2. Dry Run Validation
        $validated = $bulkService->validateDryRun($batch);
        $this->assertEquals(BulkBatchStatus::VALIDATED->value, $validated->status);
        $this->assertEquals(1, $validated->valid_items);
        $this->assertEquals(1, $validated->error_items);

        // 3. Execution of Valid Items
        $completed = $bulkService->executeBatch($validated, $user);
        $this->assertEquals(BulkBatchStatus::COMPLETED->value, $completed->status);
        $this->assertEquals(1, $completed->successful_items);
    }
}
