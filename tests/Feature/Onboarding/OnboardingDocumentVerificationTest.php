<?php

namespace Tests\Feature\Onboarding;

use App\Domains\Employee\Models\Employee;
use App\Domains\Onboarding\Enums\DocumentVerificationStatus;
use App\Domains\Onboarding\Models\HcmOnboardingCase;
use App\Domains\Onboarding\Models\HcmOnboardingDocumentRequirement;
use App\Domains\Onboarding\Models\HcmOnboardingTemplate;
use App\Domains\Onboarding\Models\HcmOnboardingTemplateVersion;
use App\Domains\Onboarding\Services\OnboardingDocumentService;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingDocumentVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_document_submission_verification_and_rejection(): void
    {
        $tenant = Tenant::factory()->create();
        $hrReviewer = User::factory()->create(['tenant_id' => $tenant->id]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-DOC-01',
            'employee_number' => 'EMP-DOC-01',
            'first_name' => 'Sara',
            'last_name' => 'Connor',
            'official_email' => 'sara@example.com',
            'joining_date' => now()->toDateString(),
            'employment_status' => 'active',
        ]);

        $template = HcmOnboardingTemplate::create(['tenant_id' => $tenant->id, 'name' => 'T', 'code' => 'T-DOC']);
        $version = HcmOnboardingTemplateVersion::create(['tenant_id' => $tenant->id, 'template_id' => $template->id, 'version_number' => 1]);

        $case = HcmOnboardingCase::create([
            'tenant_id' => $tenant->id,
            'case_number' => 'ONB-DOC-01',
            'employee_id' => $employee->id,
            'template_version_id' => $version->id,
            'start_date' => now()->toDateString(),
        ]);

        $req = HcmOnboardingDocumentRequirement::create([
            'tenant_id' => $tenant->id,
            'case_id' => $case->id,
            'document_type' => 'id_proof',
            'title' => 'Passport Verification',
            'status' => 'pending',
        ]);

        $service = new OnboardingDocumentService();

        // 1. Employee submits document
        $submitted = $service->submitDocument($req, 'documents/onboarding/passport.pdf', 'passport.pdf');
        $this->assertEquals(DocumentVerificationStatus::SUBMITTED->value, $submitted->status);
        $this->assertNotNull($submitted->submitted_at);

        // 2. Reviewer rejects document due to poor image quality
        $rejected = $service->rejectDocument($submitted, $hrReviewer->id, 'Image is blurred, please re-upload clear scan.');
        $this->assertEquals(DocumentVerificationStatus::REJECTED->value, $rejected->status);
        $this->assertCount(1, $rejected->reviews);
        $this->assertEquals('Image is blurred, please re-upload clear scan.', $rejected->reviews->first()->comments);

        // 3. Employee re-submits and reviewer verifies
        $reSubmitted = $service->submitDocument($rejected, 'documents/onboarding/passport_hd.pdf', 'passport_hd.pdf');
        $verified = $service->verifyDocument($reSubmitted, $hrReviewer->id, 'Verified successfully.');
        $this->assertCount(2, $verified->fresh()->reviews);
    }
}
