<?php

namespace Tests\Feature\Offboarding;

use App\Domains\Employee\Models\Employee;
use App\Domains\Offboarding\Models\SeparationRequest;
use App\Domains\Offboarding\Models\SeparationType;
use App\Domains\Offboarding\Services\SeparationDocumentService;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeparationDocumentGenerationAndPostExitPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_document_issuance_exit_interview_and_post_exit_access(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-DOC-29',
            'employee_number' => 'EMP-DOC-29',
            'first_name' => 'Toby',
            'last_name' => 'Flenderson',
            'official_email' => 'toby@example.com',
            'joining_date' => now()->subYears(4)->toDateString(),
            'employment_status' => 'separated',
            'termination_date' => now()->toDateString(),
        ]);
        $employee->update(['user_id' => $user->id]);

        $type = SeparationType::create([
            'tenant_id' => $tenant->id,
            'code' => 'RESIGNATION',
            'name' => 'Resignation',
        ]);

        $request = SeparationRequest::create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'separation_type_id' => $type->id,
            'request_number' => 'SEP-DOC-01',
            'status' => 'exited',
            'requested_by' => $user->id,
            'requested_at' => now(),
            'proposed_last_working_day' => now()->toDateString(),
            'actual_last_working_day' => now()->toDateString(),
            'effective_date' => now()->toDateString(),
        ]);

        // 1. Generate Exit Documents
        $docService = new SeparationDocumentService();
        $relievingDoc = $docService->generateDocument($request, 'relieving_letter', 'Official Relieving Certificate');
        $experienceDoc = $docService->generateDocument($request, 'experience_certificate', 'Experience & Service Certificate');

        $this->assertNotNull($relievingDoc);
        $this->assertEquals('relieving_letter', $relievingDoc->document_type);
        $this->assertCount(2, $request->fresh()->documents);

        // 2. Post-exit portal access for separated employee
        $response = $this->actingAs($user)->getJson('/api/v1/me/separation');
        $response->assertStatus(200);
        $response->assertJsonFragment(['request_number' => 'SEP-DOC-01']);

        // 3. Submit Exit Interview
        $interviewResponse = $this->actingAs($user)->postJson("/api/v1/me/separation/{$request->id}/exit-interview", [
            'responses' => [
                'reason_for_leaving' => 'Relocating to Costa Rica',
                'recommend_company' => 'Yes, great colleagues',
            ],
            'primary_reason_category' => 'relocation',
            'overall_sentiment' => 'positive',
            'is_anonymous' => false,
        ]);
        $interviewResponse->assertStatus(200);
        $this->assertNotNull($request->fresh()->exitInterview);
        $this->assertEquals('positive', $request->fresh()->exitInterview->overall_sentiment);
    }
}
