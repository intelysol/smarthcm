<?php

namespace Tests\Feature\Learning;

use App\Domains\Employee\Models\Employee;
use App\Domains\Learning\Models\LearningCertificate;
use App\Domains\Learning\Models\LearningCourse;
use App\Domains\Organization\Models\BusinessUnit;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class LearningPublicCertificateVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_certificate_verification_api_returns_sanitized_data(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $bu = BusinessUnit::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'name' => 'HQ Unit', 'code' => 'BU-01']);
        $dept = Department::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'business_unit_id' => $bu->id, 'department_name' => 'Engineering', 'department_code' => 'ENG']);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'department_id' => $dept->id,
            'employee_code' => 'EMP-CERT-01',
            'employee_number' => 'EMP-CERT-01',
            'first_name' => 'Alice',
            'last_name' => 'Smith',
            'official_email' => 'alice.smith@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ]);

        $course = LearningCourse::create([
            'tenant_id' => $tenant->id,
            'title' => 'Certified Cloud Architect',
            'code' => 'CCA-101',
            'delivery_type' => 'online',
            'status' => 'published',
            'duration_minutes' => 600,
        ]);

        $code = Str::random(32);
        $certificate = LearningCertificate::create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'course_id' => $course->id,
            'certificate_number' => 'CERT-2026-9999',
            'title' => 'Certified Cloud Architect Certificate',
            'issued_at' => '2026-03-01',
            'expiry_date' => '2028-03-01',
            'status' => 'active',
            'verification_code' => $code,
            'issuing_body' => 'Flow HCM Global Academy',
        ]);

        // 1. API Verification
        $response = $this->getJson("/api/v1/hcm/verify/certificate/{$code}");
        $response->assertStatus(200);
        $response->assertJson([
            'valid' => true,
            'certificate_number' => 'CERT-2026-9999',
            'course_title' => 'Certified Cloud Architect',
            'recipient_name' => 'Alice S.',
            'issuing_body' => 'Flow HCM Global Academy',
            'status' => 'active',
        ]);

        // Assure private data like email, internal id, or salary are NOT present
        $response->assertJsonMissing(['official_email' => 'alice.smith@example.com']);
        $response->assertJsonMissing(['employee_id' => $employee->id]);

        // 2. Web View Verification
        $webResponse = $this->get("/verify/certificate/{$code}");
        $webResponse->assertStatus(200);
        $webResponse->assertSee('Official Credential Verified');
        $webResponse->assertSee('CERT-2026-9999');
    }

    public function test_invalid_or_expired_certificate_verification(): void
    {
        // Non-existent code
        $response = $this->getJson('/api/v1/hcm/verify/certificate/NON_EXISTENT_CODE');
        $response->assertStatus(404);
        $response->assertJson(['valid' => false]);
    }
}
