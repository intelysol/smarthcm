<?php

declare(strict_types=1);

namespace Tests\Feature\HealthSafety;

use App\Domains\HealthSafety\Models\HcmSafetyIncident;
use App\Domains\HealthSafety\Services\SafetyInvestigationService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\BusinessUnit;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SafetyInvestigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_safety_investigation_and_root_cause_analysis(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $bu = BusinessUnit::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'name' => 'EHS BU',
            'code' => 'BU-EHS',
            'status' => 'active',
        ]);
        $dept = Department::create([
            'tenant_id' => $tenant->id,
            'business_unit_id' => $bu->id,
            'department_code' => 'DEPT-EHS',
            'department_name' => 'Safety & Compliance',
            'status' => 'active',
        ]);

        $investigator = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'department_id' => $dept->id,
            'employee_code' => 'EMP-INV-01',
            'employee_number' => 'EMP-INV-01',
            'first_name' => 'Rachel',
            'last_name' => 'Zane',
            'official_email' => 'rachel.z@example.com',
            'employment_status' => 'active',
            'joining_date' => now()->toDateString(),
        ]);

        $incident = HcmSafetyIncident::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'incident_number' => 'INC-2026-901',
            'incident_datetime' => now(),
            'incident_type' => 'near_miss',
            'severity' => 'moderate',
            'location_description' => 'Overhead crane cable vibration near catwalk',
            'description' => 'Operators noticed loose fastener on crane support track.',
            'status' => 'reported',
        ]);

        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $service = app(SafetyInvestigationService::class);

        // 1. Start investigation
        $investigation = $service->startInvestigation([
            'incident_id' => $incident->id,
            'lead_investigator_id' => $investigator->id,
            'methodology' => '5-Why Analysis',
        ], $user->id);

        $this->assertEquals('assigned', $investigation->status);
        $this->assertEquals('under_investigation', $incident->fresh()->status);

        // 2. Complete investigation
        $completed = $service->completeInvestigation($investigation, [
            'summary_of_findings' => 'Preventive maintenance schedule was deferred due to shift transition lack of handover notes.',
            'root_causes' => [
                'Deferred maintenance log omitted from shift turnover protocol',
                'Fastener torque specification verification step missing from checklist',
            ],
        ], $user->id);

        $this->assertEquals('completed', $completed->status);
        $this->assertEquals('action_pending', $incident->fresh()->status);
        $this->assertStringContainsString('Deferred maintenance log', $incident->fresh()->root_cause_summary);
    }
}
