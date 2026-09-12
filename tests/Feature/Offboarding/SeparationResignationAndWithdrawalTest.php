<?php

namespace Tests\Feature\Offboarding;

use App\Domains\Employee\Models\Employee;
use App\Domains\Offboarding\Enums\SeparationStatus;
use App\Domains\Offboarding\Models\SeparationRequest;
use App\Domains\Offboarding\Models\SeparationType;
use App\Domains\Offboarding\Services\SeparationService;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeparationResignationAndWithdrawalTest extends TestCase
{
    use RefreshDatabase;

    public function test_resignation_submission_and_withdrawal_lifecycle(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-RES-1',
            'employee_number' => 'EMP-RES-1',
            'first_name' => 'Pam',
            'last_name' => 'Beesly',
            'official_email' => 'pam@example.com',
            'joining_date' => now()->subYears(2)->toDateString(),
            'employment_status' => 'active',
        ]);
        $employee->update(['user_id' => $user->id]);

        $resignationType = SeparationType::create([
            'tenant_id' => $tenant->id,
            'code' => 'RESIGNATION',
            'name' => 'Voluntary Resignation',
            'category' => 'voluntary',
            'notice_days_default' => 30,
        ]);

        $service = new SeparationService();

        // 1. Submit Resignation
        $request = $service->createRequest($user, [
            'employee_id' => $employee->id,
            'separation_type_id' => $resignationType->id,
            'proposed_last_working_day' => now()->addDays(30)->toDateString(),
            'reason' => 'Pursuing art school degree',
            'source' => 'self_service',
        ]);

        $this->assertInstanceOf(SeparationRequest::class, $request);
        $this->assertMatchesRegularExpression('/^SEP-\d{4}-\d{6}$/', $request->request_number);
        $this->assertEquals(SeparationStatus::DRAFT->value, $request->status);

        // 2. Submit for approval
        $submitted = $service->submitRequest($request, $user);
        $this->assertEquals(SeparationStatus::PENDING_APPROVAL->value, $submitted->status);
        $this->assertNotNull($submitted->submitted_at);

        // 3. Withdraw Resignation before approval
        $withdrawn = $service->withdrawRequest($submitted, $user, 'Decided to defer art school to next year');
        $this->assertEquals(SeparationStatus::CANCELLED->value, $withdrawn->status);
        $this->assertStringContainsString('Withdrawn by employee', $withdrawn->comments);
    }
}
