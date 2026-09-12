<?php

namespace Tests\Feature\Lifecycle;

use App\Domains\Employee\Models\Employee;
use App\Domains\Lifecycle\Models\PersonnelActionChange;
use App\Domains\Lifecycle\Models\PersonnelActionRequest;
use App\Domains\Lifecycle\Models\PersonnelActionType;
use App\Domains\Lifecycle\Services\PersonnelActionAiService;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonnelActionAiAdvisoryAndGuardrailsTest extends TestCase
{
    use RefreshDatabase;

    public function test_ai_summarization_letter_drafting_and_guardrails(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-AI-1',
            'employee_number' => 'EMP-AI-1',
            'first_name' => 'Phyllis',
            'last_name' => 'Vance',
            'official_email' => 'phyllis@example.com',
            'joining_date' => now()->toDateString(),
            'employment_status' => 'active',
        ]);

        $actionType = PersonnelActionType::create([
            'tenant_id' => $tenant->id,
            'code' => 'PROMOTION',
            'name' => 'Senior Sales Executive Promotion',
        ]);

        $request = PersonnelActionRequest::create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'action_type_id' => $actionType->id,
            'request_number' => 'PA-AI-01',
            'status' => 'approved',
            'requested_by' => $user->id,
            'requested_at' => now(),
            'effective_date' => now()->toDateString(),
        ]);

        PersonnelActionChange::create([
            'tenant_id' => $tenant->id,
            'personnel_action_request_id' => $request->id,
            'field_name' => 'designation_id',
            'old_value_label' => 'Sales Associate',
            'new_value_label' => 'Senior Sales Executive',
        ]);

        $ai = new PersonnelActionAiService();

        // 1. Summarize Action
        $summary = $ai->summarizeAction($request);
        $this->assertStringContainsString('PA-AI-01', $summary['summary']);
        $this->assertTrue($summary['is_advisory']);

        // 2. Draft Letter
        $letter = $ai->draftPromotionLetter($request);
        $this->assertStringContainsString('Phyllis', $letter['body']);
        $this->assertTrue($letter['is_advisory']);

        // 3. Safety Guardrail: Adverse decision blocked
        $blocked = $ai->processAiInquiry($tenant->id, 'Please promote automatically and increase salary by 20%');
        $this->assertEquals('blocked_by_guardrails', $blocked['status']);
        $this->assertStringContainsString('Blocked by AI HCM Safety Guardrails', $blocked['error']);

        // 4. Safe inquiry allowed
        $safe = $ai->processAiInquiry($tenant->id, 'What is the required approval policy for promotions?');
        $this->assertEquals('success', $safe['status']);
    }
}
