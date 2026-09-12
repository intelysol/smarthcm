<?php

declare(strict_types=1);

namespace Tests\Feature\Performance;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Performance\Models\PerformanceCycle;
use App\Domains\Performance\Services\PerformanceFeedbackService;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Database\Seeders\PerformancePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Performance360FeedbackAnonymityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PerformancePermissionSeeder::class);
    }

    public function test_360_feedback_anonymity_threshold_enforcement(): void
    {
        $tenant = Tenant::factory()->create();
        app(\App\Domains\Platform\Contracts\TenantContext::class)->set($tenant);
        $managerUser = User::factory()->create(['tenant_id' => $tenant->id]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-360-01',
            'employee_number' => 'EMP-360-01',
            'first_name' => 'John',
            'last_name' => 'Wick',
            'official_email' => 'john.w@example.com',
            'employment_status' => 'active',
            'joining_date' => now()->toDateString(),
        ]);

        $peer1 = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-PEER-01',
            'employee_number' => 'EMP-PEER-01',
            'first_name' => 'Alice',
            'last_name' => 'Smith',
            'official_email' => 'alice@example.com',
            'employment_status' => 'active',
            'joining_date' => now()->toDateString(),
        ]);

        $peer2 = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-PEER-02',
            'employee_number' => 'EMP-PEER-02',
            'first_name' => 'Bob',
            'last_name' => 'Jones',
            'official_email' => 'bob@example.com',
            'employment_status' => 'active',
            'joining_date' => now()->toDateString(),
        ]);

        $cycle = PerformanceCycle::create([
            'tenant_id' => $tenant->id,
            'name' => '2026 360 Cycle',
            'cycle_type' => 'annual',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'status' => 'open',
        ]);

        $service = app(PerformanceFeedbackService::class);

        // 1. Request feedback from 2 peers (anonymous mode enabled)
        $req1 = $service->requestFeedback([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'employee_id' => $employee->id,
            'requested_from_employee_id' => $peer1->id,
            'relationship_type' => 'peer',
            'feedback_identity_hidden' => true,
        ], $managerUser);

        $req2 = $service->requestFeedback([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'employee_id' => $employee->id,
            'requested_from_employee_id' => $peer2->id,
            'relationship_type' => 'peer',
            'feedback_identity_hidden' => true,
        ], $managerUser);

        // 2. Submit responses from both peers
        $service->submitFeedback($req1, ['rating' => 4.5, 'response' => 'Alice detailed peer commentary.']);
        $service->submitFeedback($req2, ['rating' => 4.0, 'response' => 'Bob detailed peer commentary.']);

        // 3. View feedback where minimum anonymity threshold is 3 responses
        // Since only 2 responded (< 3 threshold), individual responses must be protected/anonymized!
        $feedbackList = $service->getFeedbackForEmployee($employee->id, $cycle->id, $managerUser, 3);

        $this->assertCount(2, $feedbackList);
        foreach ($feedbackList as $item) {
            $this->assertStringContainsString('BELOW THRESHOLD', $item->response);
            $this->assertStringNotContainsString('Alice detailed', $item->response);
            $this->assertStringNotContainsString('Bob detailed', $item->response);
        }

        // 4. View feedback with threshold of 2 (satisfies minimum threshold)
        $unmaskedList = $service->getFeedbackForEmployee($employee->id, $cycle->id, $managerUser, 2);
        $responsesConcat = $unmaskedList->pluck('response')->implode(' ');
        $this->assertStringContainsString('Alice detailed', $responsesConcat);
        $this->assertStringContainsString('Bob detailed', $responsesConcat);
    }
}
