<?php

namespace Tests\Feature\Absence;

use App\Domains\Absence\Models\HcmAbsenceEvent;
use App\Domains\Absence\Services\AdvisoryAbsenceAiService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use Carbon\Carbon;
use Database\Seeders\AttendancePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdvisoryAbsenceAiPrivacyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AttendancePermissionSeeder::class);
    }

    public function test_advisory_ai_output_contract_and_privacy_boundaries(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::query()->create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-AI-01',
            'employee_number' => '200801',
            'first_name' => 'Hina',
            'last_name' => 'Rabbani',
            'official_email' => 'hina.r@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ]);

        HcmAbsenceEvent::create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'absence_date' => '2026-11-03',
            'duration_hours' => 8.00,
            'absence_category' => 'vacation',
            'source' => 'leave',
            'is_planned' => true,
            'status' => 'reported',
            'reported_at' => now(),
        ]);

        $service = app(AdvisoryAbsenceAiService::class);
        $insights = $service->summarizeAbsenceTrends(
            $tenant->id,
            Carbon::parse('2026-11-01'),
            Carbon::parse('2026-11-07')
        );

        // Strict advisory AI governance assertions
        $this->assertTrue($insights['is_advisory_only']);
        $this->assertFalse($insights['autonomous_actions_permitted']);
        $this->assertArrayHasKey('metrics', $insights);
        $this->assertArrayHasKey('insights', $insights);

        // Privacy verification: AI insight must not contain medical terms
        $insightText = implode(' ', $insights['insights']);
        $this->assertStringNotContainsString('medical diagnosis', strtolower($insightText));
        $this->assertStringNotContainsString('disability', strtolower($insightText));
    }
}