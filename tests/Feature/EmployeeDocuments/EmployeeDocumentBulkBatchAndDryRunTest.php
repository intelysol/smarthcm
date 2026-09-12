<?php

namespace Tests\Feature\EmployeeDocuments;

use App\Domains\Employee\Models\Employee;
use App\Domains\EmployeeDocuments\Jobs\ProcessBulkEmployeeDocumentsJob;
use App\Domains\EmployeeDocuments\Models\EmployeeDocument;
use App\Domains\EmployeeDocuments\Models\EmployeeDocumentBulkBatch;
use App\Domains\EmployeeDocuments\Models\HcmDocumentCategory;
use App\Domains\EmployeeDocuments\Models\HcmDocumentType;
use App\Domains\EmployeeDocuments\Services\EmployeeDocumentBulkService;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeDocumentBulkBatchAndDryRunTest extends TestCase
{
    use RefreshDatabase;

    public function test_bulk_batch_dry_run_validation_and_processing(): void
    {
        $tenant = Tenant::factory()->create();
        $admin = User::factory()->create(['tenant_id' => $tenant->id, 'is_platform_admin' => true]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $emp1 = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-BULK-1',
            'employee_number' => 'EMP-BULK-1',
            'first_name' => 'Angela',
            'last_name' => 'Martin',
            'official_email' => 'angela@example.com',
            'joining_date' => now()->toDateString(),
            'employment_status' => 'active',
        ]);

        $category = HcmDocumentCategory::create([
            'tenant_id' => $tenant->id,
            'code' => 'COMPENSATION',
            'name' => 'Compensation',
        ]);

        $docType = HcmDocumentType::create([
            'tenant_id' => $tenant->id,
            'category_id' => $category->id,
            'code' => 'PAYSLIP',
            'name' => 'Monthly Payslip',
        ]);

        $bulkService = new EmployeeDocumentBulkService();

        $items = [
            ['employee_identifier' => 'EMP-BULK-1', 'file_name' => 'EMP-BULK-1_payslip.pdf'], // valid
            ['employee_identifier' => 'EMP-NONEXISTENT', 'file_name' => 'nonexistent_payslip.pdf'], // invalid employee
            ['employee_identifier' => 'EMP-BULK-1', 'file_name' => 'unsupported_script.exe'], // invalid file format
        ];

        // 1. Dry Run Validation
        $batch = $bulkService->validateBatch($admin, $docType, $items);
        $this->assertInstanceOf(EmployeeDocumentBulkBatch::class, $batch);
        $this->assertEquals(3, $batch->total_items);
        $this->assertEquals(1, $batch->valid_items);
        $this->assertEquals(2, $batch->error_items);
        $this->assertEquals('validated', $batch->status);

        // 2. Process Batch
        $job = new ProcessBulkEmployeeDocumentsJob($batch->id);
        $job->handle($bulkService);

        $this->assertEquals('completed', $batch->fresh()->status);
        $this->assertEquals(1, $batch->fresh()->processed_items);

        // Verify document was created for valid item
        $this->assertTrue(EmployeeDocument::where('employee_id', $emp1->id)->where('source', 'bulk_upload')->exists());
    }
}
