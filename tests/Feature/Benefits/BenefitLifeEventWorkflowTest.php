<?php

namespace Tests\Feature\Benefits;

use App\Domains\Benefits\Models\BenefitLifeEventType;
use App\Domains\Benefits\Models\BenefitPlan;
use App\Domains\Benefits\Services\BenefitLifeEventService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BenefitLifeEventWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_qualifying_life_event_reporting_document_verification_and_special_enrollment(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $hrVerifier = User::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-LIFE',
            'employee_number' => '6001',
            'first_name' => 'Stanley',
            'last_name' => 'Hudson',
            'employment_status' => 'active',
            'joining_date' => '2023-01-01',
        ]);

        $lifeEventService = app(BenefitLifeEventService::class);

        // 1. Create Life Event Type (Marriage)
        $marriageType = $lifeEventService->createEventType($tenant->id, [
            'code' => 'MARRIAGE',
            'name' => 'Marriage or Domestic Partnership',
            'notification_window_days' => 30,
            'election_window_days' => 30,
            'documentation_deadline_days' => 45,
            'requires_document' => true,
        ], $hrVerifier);

        $this->assertDatabaseHas('benefit_life_event_types', ['code' => 'MARRIAGE']);

        // 2. Employee reports life event with mock document ID from Epic 2.30
        $mockDocId = (string) Str::uuid();
        $event = $lifeEventService->reportLifeEvent($employee, [
            'life_event_type_id' => $marriageType->id,
            'event_date' => now()->subDays(5)->toDateString(),
            'description' => 'Got married on vacation.',
            'document_id' => $mockDocId,
        ]);

        $this->assertEquals('submitted', $event->status);
        $this->assertEquals('submitted', $event->documentation_status);
        $this->assertNotNull($event->election_window_end);
        $this->assertNotNull($event->documentation_deadline);

        // 3. HR Verifies Life Event Document
        $verifiedEvent = $lifeEventService->verifyLifeEvent($event, $hrVerifier, true);

        $this->assertEquals('approved', $verifiedEvent->status);
        $this->assertEquals('verified', $verifiedEvent->documentation_status);
        $this->assertEquals($hrVerifier->id, $verifiedEvent->approved_by);
    }
}
