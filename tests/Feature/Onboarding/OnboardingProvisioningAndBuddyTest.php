<?php

namespace Tests\Feature\Onboarding;

use App\Domains\Employee\Models\Employee;
use App\Domains\Onboarding\Models\HcmOnboardingCase;
use App\Domains\Onboarding\Models\HcmOnboardingTemplate;
use App\Domains\Onboarding\Models\HcmOnboardingTemplateVersion;
use App\Domains\Onboarding\Services\OnboardingProvisioningService;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingProvisioningAndBuddyTest extends TestCase
{
    use RefreshDatabase;

    public function test_provisioning_requests_and_buddy_assignment(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $newHire = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-BUD-01',
            'employee_number' => 'EMP-BUD-01',
            'first_name' => 'Dwight',
            'last_name' => 'Schrute',
            'official_email' => 'dwight@example.com',
            'joining_date' => now()->toDateString(),
            'employment_status' => 'active',
        ]);

        $buddy = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-BUD-02',
            'employee_number' => 'EMP-BUD-02',
            'first_name' => 'Jim',
            'last_name' => 'Halpert',
            'official_email' => 'jim@example.com',
            'joining_date' => now()->subYear()->toDateString(),
            'employment_status' => 'active',
        ]);

        $template = HcmOnboardingTemplate::create(['tenant_id' => $tenant->id, 'name' => 'T', 'code' => 'T-BUD']);
        $version = HcmOnboardingTemplateVersion::create(['tenant_id' => $tenant->id, 'template_id' => $template->id, 'version_number' => 1]);

        $case = HcmOnboardingCase::create([
            'tenant_id' => $tenant->id,
            'case_number' => 'ONB-BUD-01',
            'employee_id' => $newHire->id,
            'template_version_id' => $version->id,
            'start_date' => now()->toDateString(),
        ]);

        $service = new OnboardingProvisioningService();

        // 1. Provisioning Request (Laptop & Cloud Access)
        $req = $service->requestProvisioning($case, 'laptop_equipment', 'MacBook Pro 16-inch M3 Max', [
            'ram' => '64GB',
            'storage' => '1TB',
            'os' => 'macOS Sonoma',
        ]);

        $this->assertEquals('laptop_equipment', $req->request_type);
        $this->assertEquals('pending', $req->status);
        $this->assertEquals('64GB', $req->specifications['ram']);

        // 2. Buddy Assignment
        $assignment = $service->assignBuddy($case, $buddy, now()->addMonths(3)->toDateString());
        $this->assertEquals($buddy->id, $assignment->buddy_employee_id);
        $this->assertEquals('active', $assignment->status);
    }
}
