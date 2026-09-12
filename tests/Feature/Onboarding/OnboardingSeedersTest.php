<?php

namespace Tests\Feature\Onboarding;

use App\Domains\Onboarding\Models\HcmOnboardingForm;
use App\Domains\Onboarding\Models\HcmOnboardingTemplate;
use App\Domains\Shared\Models\Permission;
use App\Domains\Shared\Models\Tenant;
use Database\Seeders\OnboardingDefaultTemplateSeeder;
use Database\Seeders\OnboardingPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingSeedersTest extends TestCase
{
    use RefreshDatabase;

    public function test_onboarding_seeders_execution(): void
    {
        $tenant = Tenant::factory()->create();

        // 1. Run Permissions Seeder
        $this->seed(OnboardingPermissionSeeder::class);
        $this->assertTrue(Permission::where('name', 'hcm.onboarding.view')->exists());
        $this->assertTrue(Permission::where('name', 'hcm.onboarding.manage')->exists());
        $this->assertTrue(Permission::where('name', 'hcm.onboarding.document.verify')->exists());
        $this->assertTrue(Permission::where('name', 'hcm.self.onboarding.view')->exists());

        // 2. Run Default Template Seeder
        $this->seed(OnboardingDefaultTemplateSeeder::class);
        $this->assertTrue(HcmOnboardingTemplate::where('tenant_id', $tenant->id)->where('code', 'TMPL-CORP-STD')->exists());
        $this->assertTrue(HcmOnboardingForm::where('tenant_id', $tenant->id)->where('code', 'FORM-DIRECT-DEPOSIT')->exists());
        $this->assertTrue(HcmOnboardingForm::where('tenant_id', $tenant->id)->where('code', 'FORM-EMERGENCY-CONTACT')->exists());
    }
}
