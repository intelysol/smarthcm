<?php

namespace Tests\Feature\Engagement;

use App\Domains\Employee\Models\Employee;
use App\Domains\Engagement\Jobs\SendSurveyReminderJob;
use App\Domains\Engagement\Models\EngagementCampaign;
use App\Domains\Engagement\Models\EngagementCampaignRecipient;
use App\Domains\Engagement\Models\EngagementSurvey;
use App\Domains\Engagement\Models\EngagementSurveyQuestion;
use App\Domains\Engagement\Services\EngagementCampaignService;
use App\Domains\Organization\Models\Company;
use App\Domains\Platform\Contracts\TenantContext;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Database\Seeders\EngagementPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PulseSurveyAndRecurringCampaignTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(EngagementPermissionSeeder::class);
    }

    public function test_pulse_campaign_with_reminders_and_schedule(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantContext::class)->set($tenant);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $admin = User::factory()->create(['tenant_id' => $tenant->id]);
        $empUser = User::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::query()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $empUser->id,
            'company_id' => $company->id,
            'employee_number' => 'EMP-PULSE-01',
            'employee_code' => 'EMP-PULSE-01',
            'first_name' => 'Usman',
            'last_name' => 'Tariq',
            'joining_date' => now()->toDateString(),
        ]);

        $survey = EngagementSurvey::query()->create([
            'tenant_id' => $tenant->id,
            'code' => 'SURV-PULSE-WK',
            'title' => 'Weekly Pulse Check',
            'survey_type' => 'pulse',
            'confidentiality_type' => 'anonymous',
        ]);

        $campaignService = app(EngagementCampaignService::class);

        $campaign = $campaignService->createCampaign($tenant->id, $survey, [
            'code' => 'CAMP-PULSE-W12',
            'name' => 'Week 12 Pulse Check',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(5)->toDateString(),
            'is_recurring' => true,
            'schedule' => [
                'frequency' => 'weekly',
                'cron_expression' => '0 9 * * 1',
                'next_run_at' => now()->addDays(7),
            ],
        ], $admin->id);

        $this->assertTrue($campaign->is_recurring);
        $this->assertDatabaseHas('engagement_campaign_schedules', [
            'campaign_id' => $campaign->id,
            'frequency' => 'weekly',
        ]);

        // Launch campaign
        $campaignService->launchCampaign($campaign, $admin->id);

        $recipient = EngagementCampaignRecipient::query()->where('campaign_id', $campaign->id)->first();
        $this->assertNotNull($recipient);
        $this->assertEquals(0, $recipient->reminder_count);

        // Send reminders
        $remindedCount = $campaignService->sendReminders($campaign);
        $this->assertEquals(1, $remindedCount);

        $recipient->refresh();
        $this->assertEquals(1, $recipient->reminder_count);
        $this->assertNotNull($recipient->last_reminded_at);

        // Test Reminder Job Execution
        $job = new SendSurveyReminderJob($tenant->id, $campaign->id);
        $job->handle($campaignService);

        $recipient->refresh();
        $this->assertEquals(2, $recipient->reminder_count);
    }
}
