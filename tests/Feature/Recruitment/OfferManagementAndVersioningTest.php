<?php

namespace Tests\Feature\Recruitment;

use App\Domains\Recruitment\Enums\OfferStatus;
use App\Domains\Recruitment\Models\HcmRecruitmentApplication;
use App\Domains\Recruitment\Models\HcmRecruitmentCandidate;
use App\Domains\Recruitment\Models\HcmRecruitmentOffer;
use App\Domains\Recruitment\Models\HcmRecruitmentRequisition;
use App\Domains\Recruitment\Services\OfferManagementService;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfferManagementAndVersioningTest extends TestCase
{
    use RefreshDatabase;

    public function test_offer_drafting_versioning_approval_and_acceptance(): void
    {
        $tenant = Tenant::factory()->create();
        $recruiter = User::factory()->create(['tenant_id' => $tenant->id]);
        $financeApprover = User::factory()->create(['tenant_id' => $tenant->id]);

        $requisition = HcmRecruitmentRequisition::create([
            'tenant_id' => $tenant->id,
            'requisition_number' => 'REQ-OFR-01',
            'title' => 'Principal Engineer',
            'status' => 'open',
        ]);

        $candidate = HcmRecruitmentCandidate::create([
            'tenant_id' => $tenant->id,
            'candidate_number' => 'CAN-OFR-01',
            'first_name' => 'Linus',
            'last_name' => 'Torvalds',
            'email' => 'linus@example.com',
            'status' => 'active',
        ]);

        $application = HcmRecruitmentApplication::create([
            'tenant_id' => $tenant->id,
            'application_number' => 'APP-OFR-01',
            'candidate_id' => $candidate->id,
            'requisition_id' => $requisition->id,
            'status' => 'interview',
            'applied_at' => now(),
        ]);

        $service = new OfferManagementService();

        // 1. Create Initial Offer (v1)
        $offer = $service->createOffer($application, [
            'base_salary' => 180000.00,
            'bonus_amount' => 20000.00,
            'start_date' => now()->addMonth()->toDateString(),
            'expiry_date' => now()->addDays(14)->toDateString(),
        ], $recruiter->id);

        $this->assertInstanceOf(HcmRecruitmentOffer::class, $offer);
        $this->assertEquals(1, $offer->current_version);
        $this->assertCount(1, $offer->versions);

        // 2. Negotiation: Create New Offer Version (v2)
        $updated = $service->createNewOfferVersion($offer, [
            'base_salary' => 195000.00,
            'bonus_amount' => 25000.00,
        ], 'Candidate counter-offered based on competing enterprise offer.', $recruiter->id);

        $this->assertEquals(2, $updated->current_version);
        $this->assertEquals(195000.00, $updated->base_salary);
        $this->assertCount(2, $updated->fresh()->versions);

        // 3. Finance Approval
        $approved = $service->approveOffer($updated, $financeApprover->id, 'finance', 'Compensation committee approved revision.');
        $this->assertEquals(OfferStatus::APPROVED->value, $approved->status);

        // 4. Send to Candidate
        $sent = $service->sendOfferToCandidate($approved);
        $this->assertEquals(OfferStatus::SENT->value, $sent->status);
        $this->assertNotNull($sent->sent_at);

        // 5. Candidate Accepts
        $accepted = $service->acceptOffer($sent);
        $this->assertEquals(OfferStatus::ACCEPTED->value, $accepted->status);
        $this->assertNotNull($accepted->accepted_at);
    }
}
