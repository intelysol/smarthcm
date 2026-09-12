<?php

namespace Tests\Feature\Recruitment;

use App\Domains\Recruitment\Models\HcmRecruitmentCandidate;
use App\Domains\Recruitment\Models\HcmRecruitmentTalentPool;
use App\Domains\Recruitment\Services\CandidateService;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CandidateManagementAndDuplicateDetectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_candidate_creation_duplicate_detection_and_talent_pools(): void
    {
        $tenant = Tenant::factory()->create();
        $service = new CandidateService();

        // 1. Create candidate
        $candidate = $service->createCandidate([
            'tenant_id' => $tenant->id,
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane.doe@example.com',
            'phone' => '+1-555-0199',
            'location' => 'San Francisco, CA',
            'headline' => 'Senior Backend Engineer',
            'skills' => ['PHP', 'Laravel', 'PostgreSQL'],
            'tags' => ['High Potential', 'Immediate Availability'],
        ]);

        $this->assertInstanceOf(HcmRecruitmentCandidate::class, $candidate);
        $this->assertStringStartsWith('CAN-', $candidate->candidate_number);
        $this->assertEquals('given', $candidate->consent_status);
        $this->assertNotNull($candidate->profile);
        $this->assertCount(2, $candidate->tags);

        // 2. Talent pool assignment
        $pool = HcmRecruitmentTalentPool::create([
            'tenant_id' => $tenant->id,
            'name' => 'Senior Engineering Talent',
            'job_family' => 'Engineering',
        ]);
        $service->addToTalentPool($candidate, $pool);
        $this->assertTrue($pool->candidates()->where('candidate_id', $candidate->id)->exists());

        // 3. Duplicate detection
        $duplicates = $service->detectDuplicates($tenant->id, 'JANE.DOE@example.com', '5550199');
        $this->assertCount(1, $duplicates);
        $this->assertEquals($candidate->id, $duplicates->first()->id);

        $noDuplicates = $service->detectDuplicates($tenant->id, 'other.person@example.com', '9999999');
        $this->assertCount(0, $noDuplicates);
    }
}
