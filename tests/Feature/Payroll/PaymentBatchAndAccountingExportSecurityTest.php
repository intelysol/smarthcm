<?php

namespace Tests\Feature\Payroll;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Payroll\Models\CompensationStructure;
use App\Domains\Payroll\Models\PayrollPaymentBatch;
use App\Domains\Payroll\Models\PayrollPeriod;
use App\Domains\Payroll\Models\PayrollRun;
use App\Domains\Payroll\Services\CompensationService;
use App\Domains\Payroll\Services\PaymentBatchService;
use App\Domains\Payroll\Services\PayrollAccountingService;
use App\Domains\Payroll\Services\PayrollAuthorizationService;
use App\Domains\Payroll\Services\PayrollPeriodService;
use App\Domains\Payroll\Services\PayrollRunService;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Database\Seeders\PayrollDefaultDataSeeder;
use Database\Seeders\PayrollPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentBatchAndAccountingExportSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PayrollPermissionSeeder::class);
    }

    public function test_payment_batch_csv_generation_and_accounting_journal_voucher(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $employee = $this->createEmployee($tenant->id, $company->id);

        $seeder = new PayrollDefaultDataSeeder();
        $seeder->seedTenantData($tenant->id);

        $compService = app(CompensationService::class);
        $structure = CompensationStructure::query()->where('tenant_id', $tenant->id)->first();
        $compService->assignCompensation($employee, [
            'compensation_structure_id' => $structure->id,
            'base_salary' => 5000.00,
            'effective_from' => '2026-01-01',
        ], []);

        $periodService = app(PayrollPeriodService::class);
        $period = $periodService->createPeriod($tenant->id, [
            'period_name' => 'September 2026',
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-30',
        ]);

        $runService = app(PayrollRunService::class);
        $run = $runService->createRun($period, ['name' => 'September Run']);
        $runService->calculateRun($run);

        // 1. Bank Payment Batch Generation
        $paymentService = app(PaymentBatchService::class);
        $batch = $paymentService->createPaymentBatch($run);

        $this->assertEquals(1, $batch->total_records);
        $this->assertGreaterThan(0, (float) $batch->total_amount);

        $csvContent = $paymentService->generateExportFile($batch);
        $this->assertStringContainsString('Batch Number', $csvContent);
        $this->assertStringContainsString('Employee Number', $csvContent);
        $this->assertStringContainsString($employee->first_name, $csvContent);

        // 2. Accounting Double-Entry Journal Voucher Export
        $accountingService = app(PayrollAccountingService::class);
        $jv = $accountingService->generateAccountingExport($run);

        $this->assertEquals('exported', $jv->status);
        $this->assertEquals((float) $jv->total_debits, (float) $jv->total_credits);
        $this->assertGreaterThan(0, (float) $jv->total_debits);

        // 3. RBAC & Cross-Tenant Security Isolation
        $otherTenant = Tenant::factory()->create();
        $otherUser = User::factory()->create(['tenant_id' => $otherTenant->id]);

        $authService = app(PayrollAuthorizationService::class);
        $this->assertFalse($authService->canViewPayroll($otherUser, $tenant->id));
        $this->assertFalse($authService->canExportAccounting($otherUser, $run));
    }

    protected function createEmployee(string $tenantId, string $companyId, array $attributes = []): Employee
    {
        return Employee::query()->create(array_merge([
            'tenant_id' => $tenantId,
            'company_id' => $companyId,
            'employee_code' => 'EMP-' . uniqid(),
            'employee_number' => 'EMP-' . rand(1000, 9999),
            'first_name' => 'Henry',
            'last_name' => 'Ford',
            'official_email' => 'henry.' . uniqid() . '@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ], $attributes));
    }
}
