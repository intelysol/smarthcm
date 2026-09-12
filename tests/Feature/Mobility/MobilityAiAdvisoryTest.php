<?php

namespace Tests\Feature\Mobility;

use App\Domains\Employee\Models\Employee;
use App\Domains\Mobility\Models\MobilityAssignment;
use App\Domains\Mobility\Services\MobilityAiAdvisoryService;
use App\Domains\Mobility\Services\MobilityRelocationService;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobilityAiAdvisoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_ai_advisor_generates_strict_advisory_only_briefings_and_identifies_gaps(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-7001',
            'employee_number' => 'EMP-7001',
            'first_name' => 'Alan',
            'last_name' => 'Turing',
            'official_email' => 'alan@example.com',
            'employment_status' => 'active',
            'joining_date' => '2025-01-01',
        ]);

        $assignment = MobilityAssignment::create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'assignment_number' => 'ASN-AI-001',
            'home_country' => 'United Kingdom',
            'host_country' => 'United States',
            'home_company_id' => $company->id,
            'host_company_id' => $company->id,
            'start_date' => '2026-10-01',
            'planned_end_date' => '2027-09-30',
        ]);

        $aiService = app(MobilityAiAdvisoryService::class);

        // Initially: no relocation case, no compliance link, no cost allocation
        $briefing = $aiService->generateBriefing($assignment);

        $this->assertTrue($briefing['is_advisory_only']);
        $this->assertNotEmpty($briefing['alerts']);
        $this->assertNotEmpty($briefing['recommendations']);

        // Check relocation gap warning
        $alertsString = implode(' ', $briefing['alerts']);
        $this->assertStringContainsString('No relocation case', $alertsString);
        $this->assertStringContainsString('visa or work permit', $alertsString);
        $this->assertStringContainsString('cost allocation', $alertsString);

        // Add relocation case and verify that alert is cleared
        $reloService = app(MobilityRelocationService::class);
        $reloService->initiateRelocationCase($assignment, []);

        $briefingAfterRelo = $aiService->generateBriefing($assignment->fresh());
        $alertsStringAfter = implode(' ', $briefingAfterRelo['alerts']);
        $this->assertStringNotContainsString('No relocation case', $alertsStringAfter);
    }
}
