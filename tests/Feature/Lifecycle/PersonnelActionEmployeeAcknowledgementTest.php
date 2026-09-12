<?php

namespace Tests\Feature\Lifecycle;

use App\Domains\Employee\Models\Employee;
use App\Domains\Lifecycle\Enums\AcknowledgementStatus;
use App\Domains\Lifecycle\Models\PersonnelActionRequest;
use App\Domains\Lifecycle\Models\PersonnelActionType;
use App\Domains\Lifecycle\Services\PersonnelActionDocumentService;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonnelActionEmployeeAcknowledgementTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_acknowledgement_and_document_generation(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-ACK-1',
            'employee_number' => 'EMP-ACK-1',
            'first_name' => 'Kevin',
            'last_name' => 'Malone',
            'official_email' => 'kevin@example.com',
            'joining_date' => now()->toDateString(),
            'employment_status' => 'active',
        ]);

        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $employee->update(['user_id' => $user->id]);

        $actionType = PersonnelActionType::create([
            'tenant_id' => $tenant->id,
            'code' => 'PROMOTION',
            'name' => 'Promotion',
        ]);

        $request = PersonnelActionRequest::create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'action_type_id' => $actionType->id,
            'request_number' => 'PA-ACK-01',
            'status' => 'executed',
            'requested_by' => $user->id,
            'requested_at' => now(),
            'effective_date' => now()->toDateString(),
        ]);

        // 1. Generate Promotion Letter
        $docService = new PersonnelActionDocumentService();
        $doc = $docService->generateLetter($request, 'promotion_letter', 'Promotion Confirmation Letter');
        $this->assertNotNull($doc);
        $this->assertEquals('promotion_letter', $doc->document_type);

        // 2. Employee queries personal actions
        $response = $this->actingAs($user)->getJson('/api/v1/me/personnel-actions');
        $response->assertStatus(200);
        $response->assertJsonFragment(['request_number' => 'PA-ACK-01']);

        // 3. Employee signs electronic acknowledgement
        $ackResponse = $this->actingAs($user)->postJson("/api/v1/me/personnel-actions/{$request->id}/acknowledge", [
            'comment' => 'Thank you for the opportunity!',
        ]);
        $ackResponse->assertStatus(200);
        $ackResponse->assertJsonFragment(['status' => AcknowledgementStatus::ACKNOWLEDGED->value]);
    }
}
