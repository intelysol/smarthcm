<?php

declare(strict_types=1);

namespace Tests\Feature\HealthSafety;

use App\Domains\HealthSafety\Models\HcmSafetyIncident;
use App\Domains\HealthSafety\Services\CorrectiveActionService;
use App\Domains\HealthSafety\Services\SafetyIncidentService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\BusinessUnit;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CorrectiveActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_capa_creation_completion_and_incident_closure(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $bu = BusinessUnit::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'name' => 'Safety BU',
            'code' => 'BU-SAF',
            'status' => 'active',
        ]);
        $dept = Department::create([
            'tenant_id' => $tenant->id,
            'business_unit_id' => $bu->id,
            'department_code' => 'DEPT-SAF',
            'department_name' => 'Safety',
            'status' => 'active',
        ]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'department_id' => $dept->id,
            'employee_code' => 'EMP-ENG-01',
            'employee_number' => 'EMP-ENG-01',
            'first_name' => 'Harvey',
            'last_name' => 'Specter',
            'official_email' => 'harvey.s@example.com',
            'employment_status' => 'active',
            'joining_date' => now()->toDateString(),
        ]);

        $incident = HcmSafetyIncident::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'incident_number' => 'INC-2026-CAPA',
            'incident_datetime' => now(),
            'incident_type' => 'hazard_unsafe_condition',
            'severity' => 'moderate',
            'location_description' => 'Unguarded pinch point on conveyor motor',
            'description' => 'Direct motor drive shaft has missing guard panel.',
            'status' => 'action_pending',
        ]);

        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $capaService = app(CorrectiveActionService::class);
        $incidentService = app(SafetyIncidentService::class);

        // 1. Create CAPA
        $action = $capaService->createAction([
            'incident_id' => $incident->id,
            'action_type' => 'corrective',
            'title' => 'Fabricate and install yellow interlocked guard panel',
            'description' => 'Install ANSI B11 compliant guard on drive assembly.',
            'assigned_to_id' => $employee->id,
            'due_date' => now()->addDays(7)->toDateString(),
        ], $user->id);

        $this->assertEquals('open', $action->status);

        // 2. Attempting to close incident while CAPA is open should throw ValidationException
        $this->expectException(ValidationException::class);
        $incidentService->closeIncident($incident, 'Trying to close early', $user->id);
    }

    public function test_capa_verification_allows_incident_closure(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $incident = HcmSafetyIncident::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'incident_number' => 'INC-2026-CAPA2',
            'incident_datetime' => now(),
            'incident_type' => 'hazard_unsafe_condition',
            'severity' => 'minor',
            'location_description' => 'Broken wet floor sign',
            'description' => 'Sign cracked',
            'status' => 'action_pending',
        ]);


        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $capaService = app(CorrectiveActionService::class);
        $incidentService = app(SafetyIncidentService::class);

        $action = $capaService->createAction([
            'incident_id' => $incident->id,
            'action_type' => 'corrective',
            'hierarchy_level' => 'administrative_controls',
            'title' => 'Replace sign',
            'description' => 'Deploy new high-visibility sign',
            'due_date' => now()->addDays(2)->toDateString(),
        ], $user->id);

        // Complete & verify
        $completed = $capaService->completeAction($action, 'New sign installed', $user->id);
        $verified = $capaService->verifyAction($completed, true, 'Verified on site', $user->id);
        $this->assertEquals('verified', $verified->status);

        // Now closure succeeds
        $closedIncident = $incidentService->closeIncident($incident, 'All actions verified and risk mitigated', $user->id);
        $this->assertEquals('closed', $closedIncident->status);
    }
}
