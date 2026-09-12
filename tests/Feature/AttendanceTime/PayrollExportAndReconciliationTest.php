<?php

namespace Tests\Feature\AttendanceTime;

use App\Domains\Attendance\Models\Timesheet;
use App\Domains\Attendance\Services\PayrollTimeExportService;
use App\Domains\Attendance\Services\PayrollTimeReconciliationService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\AttendancePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollExportAndReconciliationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AttendancePermissionSeeder::class);
    }

    public function test_payroll_time_export_and_reconciliation(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $employee = Employee::query()->create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-PAY',
            'employee_number' => '100707',
            'first_name' => 'Grace',
            'last_name' => 'Hopper',
            'official_email' => 'grace.h@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ]);

        Timesheet::query()->create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-31',
            'total_worked_minutes' => 9600, // 160 hours
            'total_regular_minutes' => 9600,
            'total_overtime_minutes' => 600, // 10 hours
            'status' => 'approved',
        ]);

        $exportService = app(PayrollTimeExportService::class);
        $export = $exportService->generatePayrollExport(
            $tenant->id,
            Carbon::parse('2026-10-01'),
            Carbon::parse('2026-10-31'),
            null,
            $user->id
        );

        $this->assertEquals(1, $export->total_employees);
        $this->assertEquals(160.00, $export->total_regular_hours);
        $this->assertEquals(10.00, $export->total_overtime_hours);

        // Reconciliation: Payroll processed 155 regular hours instead of 160 (5h discrepancy)
        $payrollProcessed = [
            [
                'employee_id' => $employee->id,
                'regular_hours' => 155.00,
                'total_overtime_hours' => 10.00,
            ],
        ];

        $reconcileService = app(PayrollTimeReconciliationService::class);
        $reconciliation = $reconcileService->reconcileExportAgainstPayrollRecords(
            $export->id,
            $payrollProcessed,
            'BATCH-2026-10',
            $user->id
        );

        $this->assertEquals('discrepancy_detected', $reconciliation->status);
        $this->assertEquals(1, $reconciliation->discrepant_records_count);
        $this->assertEquals('regular_hours_mismatch', $reconciliation->discrepancies[0]['type']);
    }
}