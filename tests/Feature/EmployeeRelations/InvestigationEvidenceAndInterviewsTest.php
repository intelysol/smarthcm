<?php

namespace Tests\Feature\EmployeeRelations;

use App\Domains\EmployeeRelations\Models\EmployeeRelationAllegation;
use App\Domains\EmployeeRelations\Models\EmployeeRelationCase;
use App\Domains\EmployeeRelations\Models\EmployeeRelationCaseParticipant;
use App\Domains\EmployeeRelations\Models\EmployeeRelationCaseType;
use App\Domains\EmployeeRelations\Services\EmployeeRelationCaseService;
use App\Domains\EmployeeRelations\Services\EvidenceService;
use App\Domains\EmployeeRelations\Services\InvestigationService;
use App\Domains\Shared\Models\Permission;
use App\Domains\Shared\Models\PermissionGroup;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Database\Seeders\EmployeeRelationsCaseTypeSeeder;
use Database\Seeders\EmployeeRelationsPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InvestigationEvidenceAndInterviewsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(EmployeeRelationsPermissionSeeder::class);
        $this->seed(EmployeeRelationsCaseTypeSeeder::class);
        Storage::fake('local');
    }

    public function test_investigation_statements_versioning_evidence_and_findings(): void
    {
        $tenant = Tenant::factory()->create();
        $investigator = User::factory()->create(['tenant_id' => $tenant->id]);
        $this->grant($investigator, [
            'hcm.employee_relations.manage',
            'hcm.employee_relations.case.view',
            'hcm.employee_relations.investigation.manage',
            'hcm.employee_relations.statement.manage',
            'hcm.employee_relations.evidence.manage',
            'hcm.employee_relations.finding.manage',
        ]);

        $caseService = app(EmployeeRelationCaseService::class);
        $invService = app(InvestigationService::class);
        $evidenceService = app(EvidenceService::class);

        $caseType = EmployeeRelationCaseType::query()->where('code', 'MISCONDUCT')->first();
        $case = $caseService->createCase($tenant->id, [
            'case_type_id' => $caseType->id,
            'title' => 'Misconduct Investigation',
            'summary' => 'Investigation into security policy breaches.',
        ], $investigator);

        // 1. Start Investigation
        $investigation = $invService->startInvestigation($case, $investigator, 'Determine scope and impact of security policy breaches.');
        $this->assertEquals('active', $investigation->status);
        $this->assertEquals(6, $investigation->steps()->count()); // 6 default plan steps

        // 2. Add Allegation
        $allegation = EmployeeRelationAllegation::query()->create([
            'tenant_id' => $tenant->id,
            'case_id' => $case->id,
            'allegation_number' => 'ALG-001',
            'title' => 'Unauthorized Export of Client List',
            'description' => 'Exported sensitive client database on Aug 24.',
        ]);

        // 3. Record Participant Statement & Test Versioning
        $participant = EmployeeRelationCaseParticipant::query()->create([
            'tenant_id' => $tenant->id,
            'case_id' => $case->id,
            'participant_type' => 'witness',
            'name' => 'John Witness',
            'email' => 'witness@test.com',
        ]);

        $statement = $invService->recordStatement($case, $participant, [
            'statement_type' => 'witness',
            'content' => 'I saw the export file on the USB stick.',
            'is_verified' => true,
        ], $investigator);

        $this->assertEquals(1, $statement->current_version);
        $this->assertEquals(1, $statement->versions()->count());

        // Update statement - ensures non-destructive audit history
        $updatedStatement = $invService->updateStatement($statement, 'I saw the export file on the USB stick at 3:30 PM specifically.', 'Added exact timestamp.', $investigator);

        $this->assertEquals(2, $updatedStatement->current_version);
        $this->assertEquals(2, $updatedStatement->versions()->count());
        $this->assertEquals('I saw the export file on the USB stick.', $updatedStatement->versions()->where('version_number', 1)->first()->content);

        // 4. Ingest Evidence with SHA-256 Checksum
        $file = UploadedFile::fake()->create('audit_log.txt', 100, 'text/plain');
        $evidence = $evidenceService->storeEvidence($case, $investigator, [
            'title' => 'System Audit Log',
            'evidence_type' => 'system_record',
            'source' => 'Server Security Logs',
        ], $file);

        $this->assertNotNull($evidence->sha256_hash);
        $this->assertEquals(64, strlen($evidence->sha256_hash));
        $this->assertEquals('retained', $evidence->status);
        $this->assertTrue($evidence->history()->where('action', 'uploaded')->exists());

        // 5. Record Finding on Allegation
        $finding = $invService->recordFinding($case, $allegation, $investigator, [
            'finding' => 'substantiated',
            'confidence' => 'high',
            'rationale' => 'Corroborated by witness statement version 2 and security audit logs.',
        ]);

        $this->assertEquals('substantiated', $finding->finding);
        $this->assertEquals('finding_recorded', $allegation->fresh()->status);
    }

    private function grant(User $user, array $permissions): void
    {
        $group = PermissionGroup::query()->firstOrCreate(['name' => 'er_test'], ['label' => 'ER Test']);
        foreach ($permissions as $p) {
            $perm = Permission::query()->firstOrCreate(['name' => $p], ['permission_group_id' => $group->id, 'label' => $p]);
            $user->permissions()->syncWithoutDetaching([$perm->id]);
        }
    }
}
