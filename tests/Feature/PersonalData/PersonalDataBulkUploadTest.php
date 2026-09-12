<?php

namespace Tests\Feature\PersonalData;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\PersonalData\Models\HcmEmployeeAddress;
use App\Domains\PersonalData\Services\PersonalDataBulkService;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonalDataBulkUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_bulk_upload_dry_run_and_commit(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'is_platform_admin' => true]);

        $emp1 = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-BLK-01',
            'employee_number' => 'EMP-BLK-01',
            'first_name' => 'John',
            'last_name' => 'Tyler',
            'official_email' => 'tyler@example.com',
            'employment_status' => 'active',
            'joining_date' => now()->toDateString(),
        ]);

        $service = app(PersonalDataBulkService::class);

        $rows = [
            [
                'employee_identifier' => 'EMP-BLK-01',
                'data' => [
                    'address_type' => 'home',
                    'address_line_1' => 'Sherwood Forest Plantation',
                    'city' => 'Charles City',
                    'country' => 'USA',
                ],
            ],
            [
                'employee_identifier' => 'NON_EXISTENT_ID',
                'data' => [
                    'address_type' => 'home',
                    'address_line_1' => 'Somewhere',
                    'city' => 'City',
                    'country' => 'USA',
                ],
            ],
        ];

        // 1. Dry run upload
        $batch = $service->uploadBatch($tenant->id, 'address', $rows, $user, true);

        $this->assertEquals(2, $batch->total_items);
        $this->assertEquals(1, $batch->valid_items);
        $this->assertEquals(1, $batch->error_items);
        $this->assertEquals('validated', $batch->status);

        // Address not inserted yet because dry_run = true
        $this->assertEquals(0, HcmEmployeeAddress::where('employee_id', $emp1->id)->count());

        // 2. Process batch
        $processed = $service->processBatch($batch->id);
        $this->assertEquals('completed', $processed->status);
        $this->assertEquals(1, $processed->processed_items);

        // Address now created
        $this->assertEquals(1, HcmEmployeeAddress::where('employee_id', $emp1->id)->count());
    }
}
