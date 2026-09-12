<?php

namespace Tests\Feature\SelfService;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Branch;
use App\Domains\Organization\Models\Company;
use App\Domains\SelfService\Services\AnnouncementService;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnouncementAndAcknowledgementTest extends TestCase
{
    use RefreshDatabase;

    public function test_announcement_targeting_and_acknowledgement(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $branch1 = Branch::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'branch_name' => 'HQ', 'branch_code' => 'BR-HQ']);
        $branch2 = Branch::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'branch_name' => 'West Coast', 'branch_code' => 'BR-WC']);

        $employeeInBranch1 = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'branch_id' => $branch1->id,
            'employee_code' => 'EMP-701',
            'employee_number' => 'EMP-701',
            'first_name' => 'Grace',
            'last_name' => 'Kelly',
            'official_email' => 'grace@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ]);

        $employeeInBranch2 = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'branch_id' => $branch2->id,
            'employee_code' => 'EMP-702',
            'employee_number' => 'EMP-702',
            'first_name' => 'Hank',
            'last_name' => 'Moody',
            'official_email' => 'hank@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ]);

        $author = User::factory()->create(['tenant_id' => $tenant->id]);
        $service = app(AnnouncementService::class);

        // 1. Create Global Announcement
        $globalAnn = $service->createAnnouncement([
            'title' => 'Company Annual Gala 2026',
            'content' => 'All employees are invited to the annual dinner.',
            'priority' => 'normal',
            'requires_acknowledgement' => false,
        ], $author);

        // 2. Create Targeted Announcement for Branch 1
        $targetedAnn = $service->createAnnouncement([
            'title' => 'Branch 1 Fire Drill Scheduled',
            'content' => 'Mandatory evacuation drill on Tuesday.',
            'priority' => 'high',
            'requires_acknowledgement' => true,
        ], $author, [
            ['audience_type' => 'branch', 'audience_id' => $branch1->id],
        ]);

        // Employee 1 sees both announcements
        $emp1Notices = $service->getVisibleAnnouncementsForEmployee($employeeInBranch1);
        $this->assertCount(2, $emp1Notices);

        // Employee 2 sees only global announcement
        $emp2Notices = $service->getVisibleAnnouncementsForEmployee($employeeInBranch2);
        $this->assertCount(1, $emp2Notices);
        $this->assertEquals($globalAnn->id, $emp2Notices->first()->id);

        // 3. Employee 1 acknowledges targeted notice
        $ack = $service->acknowledgeAnnouncement($targetedAnn, $employeeInBranch1, '10.0.0.1');
        $this->assertNotNull($ack->acknowledged_at);
        $this->assertEquals('10.0.0.1', $ack->ip_address);
        $this->assertEquals($employeeInBranch1->id, $ack->employee_id);
    }
}
