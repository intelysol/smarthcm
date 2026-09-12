<?php

namespace Tests\Feature\Offboarding;

use App\Domains\Employee\Models\Employee;
use App\Domains\Offboarding\Models\SeparationRequest;
use App\Domains\Offboarding\Models\SeparationType;
use App\Domains\Offboarding\Services\SeparationNoticePeriodService;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeparationNoticePeriodAndGardenLeaveTest extends TestCase
{
    use RefreshDatabase;

    public function test_notice_period_calculation_override_and_garden_leave(): void
    {
        $tenant = Tenant::factory()->create();
        $adminUser = User::factory()->create(['tenant_id' => $tenant->id, 'is_platform_admin' => true]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-NOT-1',
            'employee_number' => 'EMP-NOT-1',
            'first_name' => 'Andy',
            'last_name' => 'Bernard',
            'official_email' => 'andy@example.com',
            'joining_date' => now()->toDateString(),
            'employment_status' => 'active',
        ]);

        $type = SeparationType::create([
            'tenant_id' => $tenant->id,
            'code' => 'RESIGNATION',
            'name' => 'Resignation',
            'notice_days_default' => 30,
        ]);

        $gardenStart = now()->addDays(5)->toDateString();
        $gardenEnd = now()->addDays(20)->toDateString();

        $request = SeparationRequest::create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'separation_type_id' => $type->id,
            'request_number' => 'SEP-NOT-01',
            'status' => 'draft',
            'requested_by' => $adminUser->id,
            'requested_at' => now(),
            'notice_start_date' => now()->toDateString(),
            'proposed_last_working_day' => now()->addDays(30)->toDateString(),
            'effective_date' => now()->addDays(30)->toDateString(),
            'is_garden_leave' => true,
            'garden_leave_start_date' => $gardenStart,
            'garden_leave_end_date' => $gardenEnd,
        ]);

        $noticeService = new SeparationNoticePeriodService();
        $notice = $noticeService->initializeNoticePeriod($request);

        // 1. Verify default notice calculation
        $this->assertEquals(30, $notice->required_days);
        $this->assertEquals(now()->addDays(30)->toDateString(), $notice->calculated_last_working_day->toDateString());
        $this->assertTrue($request->is_garden_leave);

        // 2. Override notice period with authorized user
        $overridden = $noticeService->overrideNoticePeriod($notice, $adminUser, [
            'agreed_days' => 14,
            'reason' => 'Mutual early release agreement',
            'is_buyout' => true,
            'buyout_amount' => 2500.00,
        ]);

        $this->assertTrue($overridden->is_overridden);
        $this->assertEquals(14, $overridden->agreed_days);
        $this->assertTrue($overridden->is_buyout);
        $this->assertEquals(2500.00, (float) $overridden->buyout_amount);
        $this->assertEquals(now()->addDays(14)->toDateString(), $overridden->adjusted_last_working_day->toDateString());
    }
}
