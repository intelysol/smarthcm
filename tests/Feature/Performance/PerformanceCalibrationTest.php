<?php

declare(strict_types=1);

namespace Tests\Feature\Performance;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Performance\Models\PerformanceCalibrationRecord;
use App\Domains\Performance\Models\PerformanceCalibrationSession;
use App\Domains\Performance\Models\PerformanceCycle;
use App\Domains\Performance\Models\PerformanceReview;
use App\Domains\Performance\Services\PerformanceCalibrationService;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Database\Seeders\PerformancePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerformanceCalibrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PerformancePermissionSeeder::class);
    }

    public function test_calibration_session_rating_adjustment_and_finalization(): void
    {
        $tenant = Tenant::factory()->create();
        app(\App\Domains\Platform\Contracts\TenantContext::class)->set($tenant);
        $admin = User::factory()->create(['tenant_id' => $tenant->id, 'is_platform_admin' => true]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-CAL-01',
            'employee_number' => 'EMP-CAL-01',
            'first_name' => 'Usman',
            'last_name' => 'Ghafoor',
            'official_email' => 'usman@example.com',
            'employment_status' => 'active',
            'joining_date' => now()->toDateString(),
        ]);

        $cycle = PerformanceCycle::create([
            'tenant_id' => $tenant->id,
            'name' => '2026 Calibration Cycle',
            'cycle_type' => 'annual',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'status' => 'in_progress',
        ]);

        $review = PerformanceReview::create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'employee_id' => $employee->id,
            'review_type' => 'annual',
            'status' => 'manager_reviewed',
            'overall_rating' => 4.0,
            'calculated_rating' => 3.9,
        ]);

        $service = app(PerformanceCalibrationService::class);

        // 1. Create Calibration Session
        $session = $service->createSession([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'name' => 'Engineering Calibration Committee Session H1',
        ], $admin);

        $this->assertEquals('in_progress', $session->status);

        // 2. Committee adjusts rating from 4.0 to 4.5 with audited reason
        $record = $service->adjustRating(
            $session,
            $employee->id,
            4.5,
            'Demonstrated cross-departmental impact on security compliance that was uncaptured in initial review',
            $admin
        );

        $this->assertEquals(4.5, (float) $record->final_rating);
        $this->assertEquals(4.5, (float) $review->fresh()->final_rating);
        $this->assertEquals('calibrated', $review->fresh()->status);
        $this->assertStringContainsString('cross-departmental impact', $record->change_reason);

        // 3. Finalize Calibration Session
        $finalized = $service->finalizeSession($session, $admin);
        $this->assertEquals('completed', $finalized->status);
        $this->assertNotNull($finalized->completed_at);
    }
}
