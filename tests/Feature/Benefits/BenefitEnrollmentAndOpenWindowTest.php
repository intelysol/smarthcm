<?php

namespace Tests\Feature\Benefits;

use App\Domains\Benefits\Enums\EnrollmentStatus;
use App\Domains\Benefits\Enums\EnrollmentType;
use App\Domains\Benefits\Models\BenefitEnrollment;
use App\Domains\Benefits\Models\BenefitEnrollmentWindow;
use App\Domains\Benefits\Models\BenefitPlan;
use App\Domains\Benefits\Services\BenefitEnrollmentService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BenefitEnrollmentAndOpenWindowTest extends TestCase
{
    use RefreshDatabase;

    public function test_open_enrollment_window_and_employee_enrollment_approval(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $employee = $this->createEmployee($tenant->id, $company->id);
        $hrManager = User::factory()->create(['tenant_id' => $tenant->id]);

        $window = BenefitEnrollmentWindow::create([
            'tenant_id' => $tenant->id,
            'name' => 'Open Enrollment 2027',
            'plan_year' => 2027,
            'start_date' => now()->subDays(5)->toDateString(),
            'close_date' => now()->addDays(20)->toDateString(),
            'effective_date' => '2027-01-01',
            'status' => 'open',
        ]);

        $this->assertTrue($window->isOpen());

        $plan = BenefitPlan::create([
            'tenant_id' => $tenant->id,
            'code' => 'DENTAL-PLUS',
            'name' => 'Delta Dental Plus',
            'benefit_type' => 'dental',
            'employee_cost' => 25.0000,
            'employer_cost' => 50.0000,
            'effective_from' => '2026-01-01',
            'status' => 'active',
        ]);

        $enrollmentService = app(BenefitEnrollmentService::class);

        $enrollment = $enrollmentService->enroll($employee, $plan, [
            'benefit_enrollment_window_id' => $window->id,
            'enrollment_type' => EnrollmentType::OPEN_ENROLLMENT->value,
            'coverage_level' => 'employee_plus_spouse',
            'effective_from' => '2027-01-01',
        ]);

        $this->assertEquals(EnrollmentStatus::SUBMITTED->value, $enrollment->status);
        $this->assertEquals(25.0000, (float) $enrollment->employee_contribution);

        // HR Approves Enrollment
        $approved = $enrollmentService->approveEnrollment($enrollment, $hrManager);
        $this->assertEquals(EnrollmentStatus::APPROVED->value, $approved->status);
        $this->assertEquals($hrManager->id, $approved->approved_by);

        // Activates
        $activated = $enrollmentService->activateEnrollment($approved);
        $this->assertEquals(EnrollmentStatus::ACTIVE->value, $activated->status);
    }

    protected function createEmployee(string $tenantId, string $companyId, array $attributes = []): Employee
    {
        return Employee::create(array_merge([
            'tenant_id' => $tenantId,
            'company_id' => $companyId,
            'employee_code' => 'EMP-' . uniqid(),
            'employee_number' => 'EMP-' . rand(1000, 9999),
            'first_name' => 'Bob',
            'last_name' => 'Jones',
            'official_email' => 'bob.' . uniqid() . '@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ], $attributes));
    }
}
