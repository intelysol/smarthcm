<?php

declare(strict_types=1);

namespace Tests\Feature\HealthSafety;

use App\Domains\HealthSafety\Models\HcmSafetyIncident;
use App\Domains\HealthSafety\Services\SafetyIncidentService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\BusinessUnit;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SafetyIncidentTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_incident_and_escalate_severity(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $bu = BusinessUnit::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'name' => 'Manufacturing BU',
            'code' => 'BU-MFG',
            'status' => 'active',
        ]);
        $dept = Department::create([
            'tenant_id' => $tenant->id,
            'business_unit_id' => $bu->id,
            'department_code' => 'DEPT-MFG',
            'department_name' => 'Assembly',
            'status' => 'active',
        ]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'department_id' => $dept->id,
            'employee_code' => 'EMP-INC-01',
            'employee_number' => 'EMP-INC-01',
            'first_name' => 'Robert',
            'last_name' => 'Lang',
            'official_email' => 'robert.l@example.com',
            'employment_status' => 'active',
            'joining_date' => now()->toDateString(),
        ]);

        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $service = app(SafetyIncidentService::class);

        // 1. Report incident
        $incident = $service->reportIncident([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'incident_type' => 'injury',
            'severity_level' => 'minor',
            'title' => 'Slip and fall on wet floor near conveyor belt',
            'description' => 'Employee slipped while carrying plastic tote. Mild contusion to left knee.',
            'incident_date' => now()->toDateString(),
            'affected_employee_id' => $employee->id,
            'is_osha_recordable' => false,
            'witnesses' => [
                [
                    'external_name' => 'Mark Evans',
                    'contact_info' => '555-0199',
                    'statement' => 'I saw water leaking from the overhead coolant pipe before the fall.',
                ],
            ],
        ], $user->id);

        $this->assertEquals('reported', $incident->status);
        $this->assertCount(1, $incident->witnesses);
        $this->assertEquals('minor', $incident->severity);

        // 2. Escalate severity
        $escalated = $service->escalateSeverity($incident, 'severe', 'Follow-up MRI revealed meniscus tear requiring surgery', $user->id);
        $this->assertEquals('severe', $escalated->severity);
    }
}
