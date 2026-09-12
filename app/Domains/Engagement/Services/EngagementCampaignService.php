<?php

namespace App\Domains\Engagement\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\Engagement\Events\EngagementCampaignClosed;
use App\Domains\Engagement\Events\EngagementCampaignCreated;
use App\Domains\Engagement\Events\EngagementCampaignStarted;
use App\Domains\Engagement\Events\EngagementSurveyInvitationSent;
use App\Domains\Engagement\Events\EngagementSurveyReminderSent;
use App\Domains\Engagement\Models\EngagementCampaign;
use App\Domains\Engagement\Models\EngagementCampaignRecipient;
use App\Domains\Engagement\Models\EngagementCampaignSchedule;
use App\Domains\Engagement\Models\EngagementSurvey;
use App\Domains\Engagement\Models\EngagementSurveyAudience;
use App\Domains\Shared\Services\AuditService;
use Illuminate\Support\Facades\DB;

class EngagementCampaignService
{
    public function __construct(
        protected AuditService $audit,
        protected EngagementTokenService $tokens
    ) {}

    public function createCampaign(
        string $tenantId,
        EngagementSurvey $survey,
        array $data,
        ?int $userId = null
    ): EngagementCampaign {
        return DB::transaction(function () use ($tenantId, $survey, $data, $userId) {
            $latestVersion = $survey->versions()->first();

            $campaign = EngagementCampaign::query()->create([
                'tenant_id' => $tenantId,
                'survey_id' => $survey->id,
                'survey_version_id' => $latestVersion?->id,
                'code' => $data['code'],
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'timezone' => $data['timezone'] ?? 'UTC',
                'status' => 'draft',
                'minimum_response_threshold' => $data['minimum_response_threshold'] ?? 5,
                'is_recurring' => $data['is_recurring'] ?? false,
                'created_by' => $userId,
            ]);

            if (! empty($data['audiences']) && is_array($data['audiences'])) {
                foreach ($data['audiences'] as $aud) {
                    $this->addAudience(
                        $campaign,
                        $aud['target_type'] ?? 'all',
                        $aud['target_id'] ?? null,
                        $aud['filter_criteria'] ?? null
                    );
                }
            }

            if (! empty($data['schedule']) && is_array($data['schedule'])) {
                EngagementCampaignSchedule::query()->create([
                    'tenant_id' => $tenantId,
                    'campaign_id' => $campaign->id,
                    'frequency' => $data['schedule']['frequency'] ?? 'monthly',
                    'cron_expression' => $data['schedule']['cron_expression'] ?? null,
                    'next_run_at' => $data['schedule']['next_run_at'] ?? null,
                    'is_active' => true,
                ]);
            }

            EngagementCampaignCreated::dispatch($campaign);

            $this->audit->record(
                $tenantId,
                'EngagementCampaignCreated',
                'create_campaign',
                EngagementCampaign::class,
                (string) $campaign->id,
                $userId,
                null,
                ['code' => $campaign->code, 'name' => $campaign->name]
            );

            return $campaign;
        });
    }

    public function addAudience(
        EngagementCampaign $campaign,
        string $targetType,
        ?string $targetId = null,
        ?array $filterCriteria = null
    ): EngagementSurveyAudience {
        return EngagementSurveyAudience::query()->create([
            'tenant_id' => $campaign->tenant_id,
            'campaign_id' => $campaign->id,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'filter_criteria' => $filterCriteria,
        ]);
    }

    public function generateRecipients(EngagementCampaign $campaign): int
    {
        $audiences = $campaign->audiences;
        $employeeQuery = Employee::query()->where('tenant_id', $campaign->tenant_id);

        if ($audiences->isEmpty() || $audiences->contains('target_type', 'all')) {
            // Target all active employees in tenant
            $employees = $employeeQuery->get();
        } else {
            $employeeQuery->where(function ($q) use ($audiences) {
                foreach ($audiences as $aud) {
                    if ($aud->target_type === 'department' && $aud->target_id) {
                        $q->orWhere('department_id', $aud->target_id);
                    } elseif ($aud->target_type === 'company' && $aud->target_id) {
                        $q->orWhere('company_id', $aud->target_id);
                    } elseif ($aud->target_type === 'location' && $aud->target_id) {
                        $q->orWhere('location_id', $aud->target_id);
                    }
                }
            });
            $employees = $employeeQuery->get();
        }

        $count = 0;
        foreach ($employees as $emp) {
            $recipient = EngagementCampaignRecipient::query()->firstOrCreate(
                [
                    'tenant_id' => $campaign->tenant_id,
                    'campaign_id' => $campaign->id,
                    'employee_id' => $emp->id,
                ],
                [
                    'delivery_status' => 'pending',
                ]
            );
            if ($recipient->wasRecentlyCreated) {
                $count++;
            }
        }

        return $count;
    }

    public function launchCampaign(EngagementCampaign $campaign, ?int $userId = null): EngagementCampaign
    {
        return DB::transaction(function () use ($campaign, $userId) {
            $this->generateRecipients($campaign);

            $campaign->update([
                'status' => 'active',
            ]);

            // Mark invitations sent
            $recipients = $campaign->recipients()->whereNull('invited_at')->get();
            foreach ($recipients as $rec) {
                $rec->update([
                    'delivery_status' => 'delivered',
                    'invited_at' => now(),
                ]);
                EngagementSurveyInvitationSent::dispatch($rec);
            }

            EngagementCampaignStarted::dispatch($campaign);

            $this->audit->record(
                (string) $campaign->tenant_id,
                'EngagementCampaignStarted',
                'launch_campaign',
                EngagementCampaign::class,
                (string) $campaign->id,
                $userId,
                null,
                ['status' => 'active', 'recipients_count' => $recipients->count()]
            );

            return $campaign->fresh();
        });
    }

    public function closeCampaign(EngagementCampaign $campaign, ?int $userId = null): EngagementCampaign
    {
        return DB::transaction(function () use ($campaign, $userId) {
            $campaign->update([
                'status' => 'closed',
            ]);

            EngagementCampaignClosed::dispatch($campaign);

            $this->audit->record(
                (string) $campaign->tenant_id,
                'EngagementCampaignClosed',
                'close_campaign',
                EngagementCampaign::class,
                (string) $campaign->id,
                $userId,
                null,
                ['status' => 'closed']
            );

            return $campaign->fresh();
        });
    }

    public function sendReminders(EngagementCampaign $campaign): int
    {
        if ($campaign->status !== 'active') {
            return 0;
        }

        $pendingRecipients = $campaign->recipients()
            ->whereNull('completed_at')
            ->get();

        $count = 0;
        foreach ($pendingRecipients as $rec) {
            $rec->increment('reminder_count');
            $rec->update(['last_reminded_at' => now()]);

            EngagementSurveyReminderSent::dispatch($rec);
            $count++;
        }

        return $count;
    }
}
