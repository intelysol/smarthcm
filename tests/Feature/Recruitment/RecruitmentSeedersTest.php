<?php

namespace Tests\Feature\Recruitment;

use App\Domains\Recruitment\Models\HcmRecruitmentApplicationStage;
use App\Domains\Recruitment\Models\HcmRecruitmentJobTemplate;
use App\Domains\Recruitment\Models\HcmRecruitmentSource;
use App\Domains\Shared\Models\Permission;
use App\Domains\Shared\Models\Tenant;
use Database\Seeders\RecruitmentDefaultDataSeeder;
use Database\Seeders\RecruitmentPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecruitmentSeedersTest extends TestCase
{
    use RefreshDatabase;

    public function test_recruitment_permission_and_default_data_seeders(): void
    {
        $tenant = Tenant::factory()->create();

        // 1. Run Permission Seeder
        $this->seed(RecruitmentPermissionSeeder::class);

        $this->assertTrue(Permission::where('name', 'hcm.recruitment.view')->exists());
        $this->assertTrue(Permission::where('name', 'hcm.requisition.approve')->exists());
        $this->assertTrue(Permission::where('name', 'hcm.candidate.merge')->exists());
        $this->assertTrue(Permission::where('name', 'hcm.offer.send')->exists());
        $this->assertTrue(Permission::where('name', 'hcm.hiring.approve')->exists());

        // 2. Run Default Data Seeder
        $this->seed(RecruitmentDefaultDataSeeder::class);

        $this->assertTrue(HcmRecruitmentSource::where('tenant_id', $tenant->id)->where('code', 'CAREER_PORTAL')->exists());
        $this->assertTrue(HcmRecruitmentSource::where('tenant_id', $tenant->id)->where('code', 'REFERRAL')->exists());
        $this->assertTrue(HcmRecruitmentApplicationStage::where('tenant_id', $tenant->id)->where('code', 'NEW')->exists());
        $this->assertTrue(HcmRecruitmentApplicationStage::where('tenant_id', $tenant->id)->where('code', 'HIRED')->exists());
        $this->assertTrue(HcmRecruitmentJobTemplate::where('tenant_id', $tenant->id)->where('code', 'TMPL-SWE-SR')->exists());
    }
}
