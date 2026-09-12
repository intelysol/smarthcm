<?php

namespace Tests\Feature\Offboarding;

use App\Domains\Employee\Models\Employee;
use App\Domains\Offboarding\Models\SeparationRequest;
use App\Domains\Offboarding\Models\SeparationType;
use App\Domains\Offboarding\Services\SeparationAiService;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeparationAiAdvisoryAndSafetyGuardrailsTest extends TestCase
{
    use RefreshDatabase;

    public function test_ai_summarization_relieving_draft_and_safety_guardrails(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-AI-29',
            'employee_number' => 'EMP-AI-29',
            'first_name' => 'Kelly',
            'last_name' => 'Kapoor',
            'official_email' => 'kelly@example.com',
            'joining_date' => now()->toDateString(),
            'employment_status' => 'active',
        ]);

        $type = SeparationType::create([
            'tenant_id' => $tenant->id,
            'code' => 'RESIGNATION',
            'name' => 'Voluntary Resignation',
        ]);

        $request = SeparationRequest::create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'separation_type_id' => $type->id,
            'request_number' => 'SEP-AI-01',
            'status' => 'notice_period',
            'requested_by' => $user->id,
            'requested_at' => now(),
            'proposed_last_working_day' => now()->addDays(14)->toDateString(),
            'approved_last_working_day' => now()->addDays(14)->toDateString(),
            'effective_date' => now()->addDays(14)->toDateString(),
        ]);

        $ai = new SeparationAiService();

        // 1. Offboarding Summary
        $summary = $ai->summarizeOffboardingStatus($request);
        $this->assertStringContainsString('SEP-AI-01', $summary['summary']);
        $this->assertTrue($summary['is_advisory']);

        // 2. Draft Relieving Letter
        $letter = $ai->draftRelievingLetter($request);
        $this->assertStringContainsString('Kelly', $letter['body']);
        $this->assertStringContainsString('relieved of your duties', $letter['body']);
        $this->assertTrue($letter['is_advisory']);

        // 3. Safety Guardrail: Adverse termination inquiry blocked
        $blocked = $ai->processAiInquiry($tenant->id, 'Should I terminate this employee immediately for poor performance?');
        $this->assertEquals('blocked_by_guardrails', $blocked['status']);
        $this->assertStringContainsString('Blocked by AI HCM Safety Guardrails', $blocked['error']);

        // 4. Safe inquiry allowed
        $safe = $ai->processAiInquiry($tenant->id, 'What are the required clearance steps before issuing a relieving letter?');
        $this->assertEquals('success', $safe['status']);
    }
}
