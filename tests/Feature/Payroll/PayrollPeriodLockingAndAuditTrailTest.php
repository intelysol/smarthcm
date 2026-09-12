<?php

namespace Tests\Feature\Payroll;

use App\Domains\Payroll\Enums\PeriodStatus;
use App\Domains\Payroll\Models\PayrollPeriod;
use App\Domains\Payroll\Models\PayrollPeriodLock;
use App\Domains\Payroll\Services\PayrollPeriodService;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Database\Seeders\PayrollPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PayrollPeriodLockingAndAuditTrailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PayrollPermissionSeeder::class);
    }

    public function test_period_locking_and_audit_log_tracking(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $service = app(PayrollPeriodService::class);
        $period = $service->createPeriod($tenant->id, [
            'period_name' => 'August 2026',
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-31',
        ], $user);

        $this->assertFalse($period->isLocked());

        // 1. Lock period
        $service->lockPeriod($period, $user, 'Payroll monthly closing complete.');

        $this->assertTrue($period->fresh()->isLocked());
        $this->assertEquals(PeriodStatus::LOCKED->value, $period->fresh()->status);

        $this->assertDatabaseHas('payroll_period_locks', [
            'payroll_period_id' => $period->id,
            'action' => 'locked',
            'new_status' => PeriodStatus::LOCKED->value,
            'reason' => 'Payroll monthly closing complete.',
        ]);

        // 2. Reopen period with required reason
        $service->reopenPeriod($period, $user, 'Correction needed for missing overtime in department A.');

        $this->assertFalse($period->fresh()->isLocked());
        $this->assertEquals(PeriodStatus::UNDER_REVIEW->value, $period->fresh()->status);

        $this->assertDatabaseHas('payroll_period_locks', [
            'payroll_period_id' => $period->id,
            'action' => 'reopened',
            'new_status' => PeriodStatus::UNDER_REVIEW->value,
            'reason' => 'Correction needed for missing overtime in department A.',
        ]);
    }
}
