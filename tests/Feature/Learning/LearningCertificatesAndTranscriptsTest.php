<?php

namespace Tests\Feature\Learning;

use App\Domains\Employee\Models\Employee;
use App\Domains\Learning\Models\EmployeeLearningRecord;
use App\Domains\Learning\Models\LearningCertificate;
use App\Domains\Learning\Models\LearningCourse;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Database\Seeders\LearningPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LearningCertificatesAndTranscriptsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(LearningPermissionSeeder::class);
    }

    public function test_employee_can_view_transcript_and_certificates(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $employee = Employee::query()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'company_id' => $company->id,
            'employee_number' => 'EMP-500',
            'employee_code' => 'EMP-500',
            'first_name' => 'Waqas',
            'last_name' => 'Akram',
            'joining_date' => now()->toDateString(),
        ]);

        $course = LearningCourse::query()->create([
            'tenant_id' => $tenant->id,
            'code' => 'CRS-ARCH-01',
            'title' => 'Domain Driven Design Mastery',
            'delivery_type' => 'self_paced',
            'duration' => 12,
            'credit_points' => 15,
        ]);

        $cert = LearningCertificate::query()->create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'course_id' => $course->id,
            'certificate_number' => 'CERT-2026-DDD-XYZ123',
            'title' => 'Certificate of Mastery: DDD',
            'issued_at' => now()->toDateString(),
            'status' => 'active',
            'verification_code' => 'VERIFY999',
        ]);

        EmployeeLearningRecord::query()->create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'course_id' => $course->id,
            'certificate_id' => $cert->id,
            'completion_date' => now()->toDateString(),
            'final_score' => 95.0,
            'credits_awarded' => 15.0,
            'learning_hours' => 12.0,
            'status' => 'completed',
        ]);

        // 1. Transcript
        $transcriptResponse = $this->actingAs($user)->getJson('/api/v1/hcm/me/learning/transcript');
        $transcriptResponse->assertStatus(200);
        $this->assertEquals(1, $transcriptResponse->json('data.total_courses_completed'));
        $this->assertEquals(15.0, $transcriptResponse->json('data.total_credits'));
        $this->assertEquals(12.0, $transcriptResponse->json('data.total_learning_hours'));

        // 2. Certificates
        $certResponse = $this->actingAs($user)->getJson('/api/v1/hcm/me/learning/certificates');
        $certResponse->assertStatus(200);
        $this->assertCount(1, $certResponse->json('data'));
        $this->assertEquals('CERT-2026-DDD-XYZ123', $certResponse->json('data.0.certificate_number'));
    }
}
