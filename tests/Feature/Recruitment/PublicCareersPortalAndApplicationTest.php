<?php

namespace Tests\Feature\Recruitment;

use App\Domains\Recruitment\Models\HcmRecruitmentCandidate;
use App\Domains\Recruitment\Models\HcmRecruitmentJobPosting;
use App\Domains\Recruitment\Models\HcmRecruitmentRequisition;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicCareersPortalAndApplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_careers_browsing_and_unauthenticated_application_submission(): void
    {
        $tenant = Tenant::factory()->create();

        $requisition = HcmRecruitmentRequisition::create([
            'tenant_id' => $tenant->id,
            'requisition_number' => 'REQ-PUB-01',
            'title' => 'Frontend React Engineer',
            'status' => 'open',
        ]);

        $posting = HcmRecruitmentJobPosting::create([
            'tenant_id' => $tenant->id,
            'requisition_id' => $requisition->id,
            'title' => 'Frontend React Engineer',
            'slug' => 'frontend-react-engineer',
            'summary' => 'Build high performance React and TypeScript UI components.',
            'description' => 'Detailed responsibilities and required qualifications for React developer.',
            'status' => 'published',
            'published_at' => now(),
        ]);

        // 1. Browse public careers page
        $careersResponse = $this->get('/careers');
        $careersResponse->assertStatus(200);
        $careersResponse->assertSee('Frontend React Engineer');

        // 2. View public job detail page by slug
        $detailResponse = $this->get("/careers/{$posting->slug}");
        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('Build high performance React');

        // 3. Submit public application (unauthenticated)
        $applyResponse = $this->postJson("/api/v1/public/recruitment/postings/{$posting->id}/apply", [
            'first_name' => 'Margaret',
            'last_name' => 'Hamilton',
            'email' => 'margaret@apollo.org',
            'phone' => '+1-555-1969',
            'cover_letter' => 'Pioneered Apollo 11 guidance software engineering.',
        ]);

        $applyResponse->assertStatus(201);
        $applyResponse->assertJsonStructure(['message', 'application_number']);

        // Verify candidate and application created
        $candidate = HcmRecruitmentCandidate::where('email', 'margaret@apollo.org')->first();
        $this->assertNotNull($candidate);
        $this->assertEquals('Margaret', $candidate->first_name);
        $this->assertCount(1, $candidate->applications);
        $this->assertEquals($requisition->id, $candidate->applications->first()->requisition_id);
    }
}
