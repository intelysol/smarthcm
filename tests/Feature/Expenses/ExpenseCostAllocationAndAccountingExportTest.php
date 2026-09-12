<?php

namespace Tests\Feature\Expenses;

use App\Domains\Employee\Models\Employee;
use App\Domains\Expenses\Models\ExpenseCategory;
use App\Domains\Expenses\Services\ExpenseAccountingService;
use App\Domains\Expenses\Services\ExpenseClaimService;
use App\Domains\Organization\Models\BusinessUnit;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ExpenseCostAllocationAndAccountingExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_invalid_allocation_percentage_throws_validation_exception(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $employee = $this->createEmployee($tenant->id, $company->id);

        $bu = BusinessUnit::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'code' => 'BU-CORP1',
            'name' => 'Corporate BU 1',
        ]);

        $deptSales = Department::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'business_unit_id' => $bu->id,
            'department_code' => 'SALES1',
            'department_name' => 'Sales & Growth',
        ]);

        $category = ExpenseCategory::create([
            'tenant_id' => $tenant->id,
            'code' => 'CLIENT_ENT1',
            'name' => 'Client Gala Dinner',
            'category_type' => 'entertainment',
        ]);

        $claimService = app(ExpenseClaimService::class);
        $claim = $claimService->createClaim($employee, ['title' => 'Client Hospitality']);
        $line = $claimService->addLine($claim, [
            'expense_category_id' => $category->id,
            'expense_date' => '2026-10-10',
            'description' => 'VIP Customer Gala Event',
            'original_amount' => 1000.0000,
        ]);

        $accountingService = app(ExpenseAccountingService::class);

        $this->expectException(ValidationException::class);
        $accountingService->allocateClaimLine($line, [
            ['department_id' => $deptSales->id, 'allocation_percentage' => 60.00], // 60% != 100%
        ]);
    }

    public function test_valid_cost_allocation_and_balanced_gl_export(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $employee = $this->createEmployee($tenant->id, $company->id);
        $financeUser = User::factory()->create(['tenant_id' => $tenant->id]);

        $bu = BusinessUnit::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'code' => 'BU-CORP',
            'name' => 'Corporate BU',
        ]);

        $deptSales = Department::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'business_unit_id' => $bu->id,
            'department_code' => 'SALES',
            'department_name' => 'Sales & Growth',
        ]);

        $deptMarketing = Department::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'business_unit_id' => $bu->id,
            'department_code' => 'MKTG',
            'department_name' => 'Marketing',
        ]);

        $category = ExpenseCategory::create([
            'tenant_id' => $tenant->id,
            'code' => 'CLIENT_ENT',
            'name' => 'Client Gala Dinner',
            'category_type' => 'entertainment',
            'accounting_code' => 'GL-6107-ENT',
        ]);

        $claimService = app(ExpenseClaimService::class);
        $claim = $claimService->createClaim($employee, ['title' => 'Client Hospitality', 'claim_date' => '2026-10-10']);
        $line = $claimService->addLine($claim, [
            'expense_category_id' => $category->id,
            'expense_date' => '2026-10-10',
            'description' => 'VIP Customer Gala Event',
            'original_amount' => 1000.0000,
            'tax_rate' => 10.0000,
            'tax_amount' => 100.0000,
            'is_tax_recoverable' => true,
        ]);

        $claimService->submitClaim($claim);
        $claimService->approveByManager($claim, $financeUser);
        $claimService->approveByFinance($claim, $financeUser);

        $accountingService = app(ExpenseAccountingService::class);

        // Valid Allocation = 100% (60% Sales, 40% Marketing)
        $accountingService->allocateClaimLine($line, [
            ['department_id' => $deptSales->id, 'allocation_percentage' => 60.00],
            ['department_id' => $deptMarketing->id, 'allocation_percentage' => 40.00],
        ]);

        $this->assertEquals(2, $line->allocations()->count());
        $this->assertEquals(600.0000, (float) $line->allocations()->where('department_id', $deptSales->id)->first()->allocated_amount);
        $this->assertEquals(400.0000, (float) $line->allocations()->where('department_id', $deptMarketing->id)->first()->allocated_amount);

        // Generate Balanced GL Accounting Export
        $export = $accountingService->generateAccountingExport(
            $tenant->id,
            '2026-10-01',
            '2026-10-31',
            $financeUser
        );

        $this->assertNotNull($export->batch_number);
        $this->assertEquals('exported', $export->status);
        $this->assertTrue($export->gl_export_payload['is_balanced']);
        $this->assertEquals(1000.0000, (float) $export->total_amount);
    }

    protected function createEmployee(string $tenantId, string $companyId, array $attributes = []): Employee
    {
        return Employee::create(array_merge([
            'tenant_id' => $tenantId,
            'company_id' => $companyId,
            'employee_code' => 'EMP-' . uniqid(),
            'employee_number' => 'EMP-' . rand(1000, 9999),
            'first_name' => 'Johannes',
            'last_name' => 'Kepler',
            'official_email' => 'kepler.' . uniqid() . '@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ], $attributes));
    }
}
