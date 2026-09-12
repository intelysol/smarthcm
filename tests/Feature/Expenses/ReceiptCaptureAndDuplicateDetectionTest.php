<?php

namespace Tests\Feature\Expenses;

use App\Domains\Employee\Models\Employee;
use App\Domains\Expenses\Models\ExpenseCategory;
use App\Domains\Expenses\Models\ExpenseClaim;
use App\Domains\Expenses\Services\ExpenseClaimService;
use App\Domains\Expenses\Services\ReceiptService;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReceiptCaptureAndDuplicateDetectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_receipt_metadata_hash_and_duplicate_detection(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $employee = $this->createEmployee($tenant->id, $company->id);

        $category = ExpenseCategory::create([
            'tenant_id' => $tenant->id,
            'code' => 'SUPPLIES',
            'name' => 'Office Supplies',
            'category_type' => 'supplies',
        ]);

        $claimService = app(ExpenseClaimService::class);
        $receiptService = app(ReceiptService::class);

        // 1. Create Line 1
        $claim1 = $claimService->createClaim($employee, ['title' => 'Supplies 1']);
        $line1 = $claimService->addLine($claim1, [
            'expense_category_id' => $category->id,
            'expense_date' => '2026-09-05',
            'merchant' => 'Staples Store #12',
            'description' => 'Printer ink & paper',
            'original_amount' => 120.0000,
        ]);

        // Attach receipt
        $receipt1 = $receiptService->attachReceipt($employee, [
            'file_name' => 'staples_receipt_12.pdf',
            'file_size' => 4500,
            'content' => 'INV-889102-STAPLES-CONTENT',
            'merchant_extracted' => 'Staples Store #12',
            'invoice_number' => 'INV-889102',
            'extracted_amount' => 120.0000,
        ], $line1->id);

        $this->assertNotNull($receipt1->receipt_hash);
        $this->assertEquals('staples_receipt_12.pdf', $receipt1->file_name);

        // Check duplicate hash detection
        $isDuplicate = $receiptService->isDuplicateReceipt($tenant->id, $receipt1->receipt_hash);
        $this->assertTrue($isDuplicate);

        // 2. Create identical Line 2 (same date, merchant, amount) -> Duplicate detection warning generated
        $claim2 = $claimService->createClaim($employee, ['title' => 'Supplies 2']);
        $line2 = $claimService->addLine($claim2, [
            'expense_category_id' => $category->id,
            'expense_date' => '2026-09-05',
            'merchant' => 'Staples Store #12',
            'description' => 'Another printer ink order',
            'original_amount' => 120.0000,
        ]);

        $this->assertTrue($line2->policyResults()->where('rule_type', 'duplicate_detection')->exists());
        $this->assertTrue($claim2->exceptions()->where('exception_type', 'duplicate_expense')->exists());
    }

    protected function createEmployee(string $tenantId, string $companyId, array $attributes = []): Employee
    {
        return Employee::create(array_merge([
            'tenant_id' => $tenantId,
            'company_id' => $companyId,
            'employee_code' => 'EMP-' . uniqid(),
            'employee_number' => 'EMP-' . rand(1000, 9999),
            'first_name' => 'Grace',
            'last_name' => 'Hopper',
            'official_email' => 'grace.' . uniqid() . '@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ], $attributes));
    }
}
