<?php

namespace Tests\Feature\EmployeeAi;

use App\Domains\EmployeeAi\Contracts\EmployeeAiConciergeInterface;
use App\Domains\EmployeeAi\Models\HcmAiConciergeSession;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class EmployeeAiConciergeTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_start_session_and_answer_leave_and_policy_queries(): void
    {
        $tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Acme Global',
            'slug' => 'acme-global',
            'tenant_code' => 'ACM001',
            'status' => 'active',
        ]);

        $companyId = (string) Str::uuid();
        DB::table('companies')->insert([
            'id' => $companyId,
            'tenant_id' => $tenant->id,
            'name' => 'Acme Global Services',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $employeeId = (string) Str::uuid();
        DB::table('employees')->insert([
            'id' => $employeeId,
            'tenant_id' => $tenant->id,
            'company_id' => $companyId,
            'employee_code' => 'EMP-101',
            'employee_number' => 'EMP-101',
            'first_name' => 'Sarah',
            'last_name' => 'Connor',
            'department_id' => null,
            'employment_status' => 'ACTIVE',
            'joining_date' => '2024-03-01',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $concierge = app(EmployeeAiConciergeInterface::class);
        $userId = (string) Str::uuid();

        // 1. Start Session
        $session = $concierge->startSession($tenant->id, $userId, $employeeId, 'EMPLOYEE');
        $this->assertNotNull($session->id);
        $this->assertEquals('ACTIVE', $session->status);

        // 2. Query Leave Balances
        $resLeave = $concierge->chat($session->id, 'How many annual leaves do I have left?', $tenant->id, $userId, $employeeId);
        $this->assertStringContainsString('annual leave', strtolower($resLeave['message']['content']));
        $this->assertNotEmpty($resLeave['citations']);

        // 3. Query HR Policy (RAG with citations)
        $resPolicy = $concierge->chat($session->id, 'What is the leave carry forward policy?', $tenant->id, $userId, $employeeId);
        $this->assertStringContainsString('carry forward', strtolower($resPolicy['message']['content']));
        $this->assertEquals('Enterprise Leave Policy', $resPolicy['citations'][0]['source']);

        // 4. API Session & Chat Test
        $response = $this->withHeaders([
            'X-Tenant-ID' => $tenant->id,
            'X-User-ID' => $userId,
            'X-Employee-ID' => $employeeId,
        ])->postJson("/api/hcm/me/ai/sessions/{$session->id}/chat", [
            'prompt' => 'Show my recent payslip',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'citations',
        ]);
    }

    public function test_self_service_action_preparation_and_confirmation_workflow(): void
    {
        $tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Acme Global',
            'slug' => 'acme-global',
            'tenant_code' => 'ACM001',
            'status' => 'active',
        ]);

        $userId = (string) Str::uuid();
        $employeeId = (string) Str::uuid();

        $concierge = app(EmployeeAiConciergeInterface::class);
        $session = $concierge->startSession($tenant->id, $userId, $employeeId, 'EMPLOYEE');

        // Request leave via chat
        $chatResult = $concierge->chat($session->id, 'I want to apply for leave next week', $tenant->id, $userId, $employeeId);
        $this->assertNotNull($chatResult['action_proposal']);
        $this->assertEquals('PROPOSED', $chatResult['action_proposal']['status']);
        $actionId = $chatResult['action_proposal']['id'];

        // Confirm Action via API
        $response = $this->withHeaders([
            'X-Tenant-ID' => $tenant->id,
            'X-User-ID' => $userId,
        ])->postJson("/api/hcm/me/ai/actions/{$actionId}/confirm", [
            'comment' => 'Approved by myself for submission',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('action.status', 'SUBMITTED');
        $this->assertDatabaseHas('hcm_ai_concierge_actions', [
            'id' => $actionId,
            'status' => 'SUBMITTED',
        ]);
    }
}
