<?php

namespace Tests\Feature\EmployeeRelations;

use App\Domains\EmployeeRelations\Models\EmployeeRelationCase;
use App\Domains\EmployeeRelations\Models\EmployeeRelationCaseToken;
use App\Domains\EmployeeRelations\Models\EmployeeRelationCaseType;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use Database\Seeders\EmployeeRelationsCaseTypeSeeder;
use Database\Seeders\EmployeeRelationsPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnonymousIntakeAndTokenTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(EmployeeRelationsPermissionSeeder::class);
        $this->seed(EmployeeRelationsCaseTypeSeeder::class);
    }

    public function test_anonymous_case_intake_and_secure_token_tracking(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $ethicsCaseType = EmployeeRelationCaseType::query()->where('code', 'ETHICS')->first();

        // 1. Submit anonymous intake (No authentication required)
        $intakePayload = [
            'tenant_id' => $tenant->id,
            'case_type_id' => $ethicsCaseType->id,
            'title' => 'Anonymous Ethics Report',
            'summary' => 'Observed non-compliant procurement practices in regional warehouse.',
            'incident_location' => 'Warehouse B',
            'urgency' => 'high',
        ];

        $resp = $this->postJson('/api/v1/hcm/employee-relations/anonymous', $intakePayload);
        $resp->assertStatus(201);
        $resp->assertJsonStructure([
            'message',
            'case_number',
            'token',
            'status',
        ]);

        $rawToken = $resp->json('token');
        $caseNumber = $resp->json('case_number');

        // Verify that raw token is NOT in database, but SHA-256 hash exists
        $tokenRecord = EmployeeRelationCaseToken::query()->where('tenant_id', $tenant->id)->first();
        $this->assertNotNull($tokenRecord);
        $this->assertNotEquals($rawToken, $tokenRecord->token_hash);
        $this->assertEquals(hash('sha256', $rawToken), $tokenRecord->token_hash);

        // 2. Track case using anonymous token
        $trackResp = $this->getJson("/api/v1/hcm/employee-relations/anonymous/{$rawToken}");
        $trackResp->assertStatus(200);
        $trackResp->assertJson([
            'case_number' => $caseNumber,
            'title' => 'Anonymous Ethics Report',
            'status' => 'submitted',
        ]);

        // 3. Post follow-up message as anonymous reporter
        $msgResp = $this->postJson("/api/v1/hcm/employee-relations/anonymous/{$rawToken}/message", [
            'message' => 'Additional witness: Invoice reference #99214.',
        ]);
        $msgResp->assertStatus(201);

        $case = EmployeeRelationCase::query()->where('case_number', $caseNumber)->first();
        $this->assertTrue($case->caseNotes()->where('content', 'like', '%Invoice reference #99214%')->exists());

        // 4. Invalid token rejection
        $invalidResp = $this->getJson('/api/v1/hcm/employee-relations/anonymous/invalid_fake_token_123');
        $invalidResp->assertStatus(404);
    }
}
