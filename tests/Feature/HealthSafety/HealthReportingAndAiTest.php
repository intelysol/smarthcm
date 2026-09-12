<?php

declare(strict_types=1);

namespace Tests\Feature\HealthSafety;

use App\Domains\HealthSafety\Models\HcmSafetyIncident;
use App\Domains\HealthSafety\Services\HealthAiAdvisoryService;
use App\Domains\HealthSafety\Services\HealthReportingService;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthReportingAndAiTest extends TestCase
{
    use RefreshDatabase;

    public function test_calculate_osha_safety_kpis_and_log_generation(): void
    {
        $tenant = Tenant::factory()->create();

        // Seed 1 lost-time recordable incident and 1 non-lost-time recordable
        HcmSafetyIncident::create([
            'tenant_id' => $tenant->id,
            'company_id' => '00000000-0000-0000-0000-000000000001',
            'incident_number' => 'INC-2026-001',
            'incident_type' => 'injury',
            'severity' => 'moderate',
            'location_description' => 'Operator thumb caught in hopper',
            'description' => 'Operator thumb caught in hopper',
            'incident_datetime' => now(),
            'is_osha_reportable' => true,
            'lost_time_injury' => true,
            'lost_work_days' => 5,
            'status' => 'reported',
        ]);

        HcmSafetyIncident::create([
            'tenant_id' => $tenant->id,
            'company_id' => '00000000-0000-0000-0000-000000000001',
            'incident_number' => 'INC-2026-002',
            'incident_type' => 'injury',
            'severity' => 'minor',
            'location_description' => 'Ergonomic repetitive strain',
            'description' => 'Ergonomic repetitive strain',
            'incident_datetime' => now(),
            'is_osha_reportable' => true,
            'lost_time_injury' => false,
            'lost_work_days' => 0,
            'status' => 'reported',
        ]);

        $reporting = app(HealthReportingService::class);
        $kpis = $reporting->calculateSafetyKpis($tenant->id, null, (string) now()->year, 200000);

        // 2 recordables in 200,000 hrs => TRIR = (2 * 200,000) / 200,000 = 2.0
        // 1 lost-time in 200,000 hrs => LTIR = (1 * 200,000) / 200,000 = 1.0
        $this->assertEquals(2.0, $kpis['trir']);
        $this->assertEquals(1.0, $kpis['ltir']);
        $this->assertEquals(2, $kpis['recordable_incidents']);
        $this->assertEquals(1, $kpis['lost_time_incidents']);

        // Test OSHA 300 log
        $osha300 = $reporting->generateOsha300Log($tenant->id, (string) now()->year);
        $this->assertEquals(2, $osha300['establishment_recordable_count']);
        $this->assertEquals(1, $osha300['total_lost_time_cases']);
        $this->assertEquals(5, $osha300['total_days_away']);
    }

    public function test_ai_advisory_is_assistive_and_marks_is_advisory_flag(): void
    {
        $ai = app(HealthAiAdvisoryService::class);

        $incidentAnalysis = $ai->analyzeIncident('Worker suffered compound fracture in leg when forklift collided with pallet');
        $this->assertTrue($incidentAnalysis['is_advisory']);
        $this->assertEquals('critical', $incidentAnalysis['suggested_severity']);
        $this->assertTrue($incidentAnalysis['suggested_osha_recordable']);
        $this->assertNotEmpty($incidentAnalysis['suggested_actions']);

        $accommodationAdvice = $ai->adviseAccommodations('Employee cannot lift packages greater than 20 lbs');
        $this->assertTrue($accommodationAdvice['is_advisory']);
        $this->assertNotEmpty($accommodationAdvice['recommended_accommodations']);
    }
}
