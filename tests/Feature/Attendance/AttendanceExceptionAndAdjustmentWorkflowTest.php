<?php

namespace Tests\Feature\Attendance;

use App\Domains\Attendance\Enums\AdjustmentStatus;
use App\Domains\Attendance\Enums\ExceptionStatus;
use App\Domains\Attendance\Models\AttendanceAdjustment;
use App\Domains\Attendance\Models\AttendanceException;
use App\Domains\Attendance\Services\AttendanceAdjustmentService;
use App\Domains\Attendance\Services\AttendanceDeviceService;
use App\Domains\Attendance\Services\AttendanceProcessor;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Database\Seeders\AttendanceDefaultDataSeeder;
use Database\Seeders\AttendancePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceExceptionAndAdjustmentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AttendancePermissionSeeder::class);
    }

    public function test_missing_punch_triggers_exception_and_regularization_resolves_it(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $hrManager = User::factory()->create(['tenant_id' => $tenant->id]);

        $employee = $this->createEmployee($tenant->id, $company->id, [
            'user_id' => $user->id,
            'employee_number' => 'EMP-MISSING',
        ]);

        $seeder = new AttendanceDefaultDataSeeder();
        $seeder->seedTenantData($tenant->id);

        $deviceService = app(AttendanceDeviceService::class);
        $processor = app(AttendanceProcessor::class);
        $adjustmentService = app(AttendanceAdjustmentService::class);

        // 1. Employee only punches Clock-In (08:00 AM) and forgets Clock-Out
        $deviceService->ingestRawEvents($tenant->id, null, [
            ['employee_identifier' => 'EMP-MISSING', 'timestamp' => '2026-09-07 08:00:00', 'event_type' => 'IN'],
        ]);

        $session = $processor->processEmployeeDate($employee, '2026-09-07');
        $this->assertEquals('incomplete', $session->status);

        // 2. Exception raised
        $exception = AttendanceException::query()
            ->where('tenant_id', $tenant->id)
            ->where('employee_id', $employee->id)
            ->where('exception_type', 'missing_punch')
            ->first();

        $this->assertNotNull($exception);
        $this->assertEquals(ExceptionStatus::OPEN->value, $exception->status);
        $this->assertEquals('critical', $exception->severity);

        // 3. Employee submits Regularization / Adjustment request
        $adjustment = $adjustmentService->requestAdjustment($employee, [
            'adjustment_date' => '2026-09-07',
            'adjustment_type' => 'missed_punch',
            'requested_values' => [
                'actual_start_time' => '2026-09-07 08:00:00',
                'actual_end_time' => '2026-09-07 17:00:00',
                'net_worked_minutes' => 480,
                'status' => 'present',
            ],
            'reason' => 'Gate turnstile power failure during evening departure.',
        ], $user);

        $this->assertEquals(AdjustmentStatus::PENDING->value, $adjustment->status);

        // 4. HR Approves the Adjustment
        $adjustmentService->approveAdjustment($adjustment, $hrManager);

        $this->assertEquals(AdjustmentStatus::HR_APPROVED->value, $adjustment->fresh()->status);
        $this->assertEquals('present', $session->fresh()->status);
        $this->assertEquals(480, $session->fresh()->net_worked_minutes);
        $this->assertTrue($session->fresh()->is_adjusted);

        // 5. Exception should be auto-resolved
        $this->assertEquals(ExceptionStatus::RESOLVED->value, $exception->fresh()->status);
        $this->assertEquals('adjusted', $exception->fresh()->resolution_type);
    }

    protected function createEmployee(string $tenantId, string $companyId, array $attributes = []): Employee
    {
        return Employee::query()->create(array_merge([
            'tenant_id' => $tenantId,
            'company_id' => $companyId,
            'employee_code' => 'EMP-' . uniqid(),
            'employee_number' => 'EMP-' . rand(1000, 9999),
            'first_name' => 'John',
            'last_name' => 'Doe',
            'official_email' => 'john.' . uniqid() . '@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ], $attributes));
    }
}
