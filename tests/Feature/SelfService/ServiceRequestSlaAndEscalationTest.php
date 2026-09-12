<?php

namespace Tests\Feature\SelfService;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\SelfService\Enums\ServiceRequestStatus;
use App\Domains\SelfService\Enums\SlaStatus;
use App\Domains\SelfService\Models\HrServiceCategory;
use App\Domains\SelfService\Models\HrServiceDefinition;
use App\Domains\SelfService\Models\HrServiceSlaPolicy;
use App\Domains\SelfService\Models\HrServiceVersion;
use App\Domains\SelfService\Services\RequestEscalationService;
use App\Domains\SelfService\Services\RequestSlaService;
use App\Domains\SelfService\Services\ServiceRequestService;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceRequestSlaAndEscalationTest extends TestCase
{
    use RefreshDatabase;

    public function test_sla_initialization_pause_resume_and_breach_escalation(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        
        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-300',
            'employee_number' => 'EMP-300',
            'first_name' => 'Charlie',
            'last_name' => 'Brown',
            'official_email' => 'charlie@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ]);

        $slaPolicy = HrServiceSlaPolicy::create([
            'tenant_id' => $tenant->id,
            'code' => 'SLA-48H',
            'name' => '48 Hour Resolution',
            'response_time_minutes' => 120, // 2h
            'resolution_time_minutes' => 2880, // 48h
            'is_active' => true,
        ]);

        $category = HrServiceCategory::create([
            'tenant_id' => $tenant->id,
            'code' => 'CAT-OPS',
            'name' => 'Operations',
        ]);

        $service = HrServiceDefinition::create([
            'tenant_id' => $tenant->id,
            'hr_service_category_id' => $category->id,
            'service_code' => 'SVC-OPS',
            'name' => 'Operations Request',
            'sla_policy_id' => $slaPolicy->id,
        ]);

        $version = HrServiceVersion::create([
            'tenant_id' => $tenant->id,
            'hr_service_definition_id' => $service->id,
            'version_number' => 1,
            'effective_from' => '2026-01-01',
        ]);

        $requestService = app(ServiceRequestService::class);
        $slaService = app(RequestSlaService::class);
        $escalationService = app(RequestEscalationService::class);

        // 1. Submit request and verify SLA initialization
        $request = $requestService->createRequest($employee, $service, ['subject' => 'Badge replacement']);
        $submitted = $requestService->submitRequest($request);

        $sla = $submitted->slaInstance;
        $this->assertNotNull($sla);
        $this->assertEquals(SlaStatus::RUNNING->value, $sla->status);
        $this->assertNotNull($sla->response_due_at);
        $this->assertNotNull($sla->resolution_due_at);

        // 2. Pause SLA (Waiting for employee)
        $waiting = $requestService->transitionStatus($submitted, ServiceRequestStatus::WAITING_FOR_EMPLOYEE->value);
        $this->assertEquals(SlaStatus::PAUSED->value, $waiting->slaInstance->fresh()->status);

        // 3. Resume SLA after 60 simulated minutes
        Carbon::setTestNow(now()->addMinutes(60));
        $resumed = $requestService->transitionStatus($waiting, ServiceRequestStatus::IN_PROGRESS->value);
        $resumedSla = $resumed->slaInstance->fresh();
        $this->assertEquals(SlaStatus::RUNNING->value, $resumedSla->status);
        $this->assertEquals(60, $resumedSla->total_paused_minutes);

        // 4. Test Escalation Trigger on Breach
        Carbon::setTestNow(now()->addDays(5)); // Past SLA deadline
        $escalation = $escalationService->evaluateRequest($resumed);
        $this->assertNotNull($escalation);
        $this->assertEquals(2, $escalation->escalation_level);
        $this->assertEquals(SlaStatus::BREACHED->value, $resumed->slaInstance->fresh()->status);

        Carbon::setTestNow(); // Reset test clock
    }
}
