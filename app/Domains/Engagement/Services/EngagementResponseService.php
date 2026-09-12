<?php

namespace App\Domains\Engagement\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\Engagement\Events\EngagementResponseStarted;
use App\Domains\Engagement\Events\EngagementResponseSubmitted;
use App\Domains\Engagement\Models\EngagementCampaign;
use App\Domains\Engagement\Models\EngagementCampaignRecipient;
use App\Domains\Engagement\Models\EngagementResponse;
use App\Domains\Engagement\Models\EngagementResponseAnswer;
use App\Domains\Engagement\Models\EngagementSurveyQuestion;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class EngagementResponseService
{
    public function __construct(
        protected EngagementTokenService $tokens,
        protected EngagementPrivacyService $privacy
    ) {}

    public function startResponse(
        EngagementCampaign $campaign,
        ?Employee $employee = null,
        ?string $rawToken = null
    ): EngagementResponse {
        return DB::transaction(function () use ($campaign, $employee, $rawToken) {
            $survey = $campaign->survey;
            $isAnonymous = ($survey->confidentiality_type === 'anonymous');

            if ($isAnonymous) {
                // Anonymous response: employee_id MUST be null
                if ($rawToken && ! $this->tokens->validateToken($campaign, $rawToken)) {
                    throw new InvalidArgumentException('Invalid or expired participation token.');
                }

                $response = EngagementResponse::query()->create([
                    'tenant_id' => $campaign->tenant_id,
                    'survey_id' => $survey->id,
                    'survey_version_id' => $campaign->survey_version_id,
                    'campaign_id' => $campaign->id,
                    'employee_id' => null, // Anonymous!
                    'confidentiality_type' => 'anonymous',
                    'response_status' => 'in_progress',
                    'started_at' => now(),
                    'is_locked' => false,
                    'department_id' => $employee?->department_id,
                    'location_id' => $employee?->location_id,
                ]);

                if ($employee) {
                    // Update recipient started status without recording response link
                    EngagementCampaignRecipient::query()
                        ->where('campaign_id', $campaign->id)
                        ->where('employee_id', $employee->id)
                        ->whereNull('started_at')
                        ->update(['started_at' => now()]);
                }
            } else {
                if (! $employee) {
                    throw new InvalidArgumentException('Named survey requires an authenticated employee.');
                }

                // Check if multiple responses allowed
                if (! $survey->allow_multiple_responses) {
                    $existing = EngagementResponse::query()
                        ->where('campaign_id', $campaign->id)
                        ->where('employee_id', $employee->id)
                        ->where('response_status', 'submitted')
                        ->first();
                    if ($existing) {
                        throw new InvalidArgumentException('You have already submitted a response for this survey.');
                    }
                }

                $response = EngagementResponse::query()->create([
                    'tenant_id' => $campaign->tenant_id,
                    'survey_id' => $survey->id,
                    'survey_version_id' => $campaign->survey_version_id,
                    'campaign_id' => $campaign->id,
                    'employee_id' => $employee->id,
                    'confidentiality_type' => $survey->confidentiality_type,
                    'response_status' => 'in_progress',
                    'started_at' => now(),
                    'is_locked' => false,
                    'department_id' => $employee->department_id,
                    'location_id' => $employee->location_id,
                ]);

                EngagementCampaignRecipient::query()
                    ->where('campaign_id', $campaign->id)
                    ->where('employee_id', $employee->id)
                    ->whereNull('started_at')
                    ->update(['started_at' => now()]);
            }

            EngagementResponseStarted::dispatch($response);

            return $response;
        });
    }

    public function submitResponse(
        EngagementResponse $response,
        array $answers,
        ?string $rawToken = null,
        ?Employee $completingEmployee = null
    ): EngagementResponse {
        return DB::transaction(function () use ($response, $answers, $rawToken, $completingEmployee) {
            if ($response->is_locked || $response->response_status === 'submitted') {
                throw new InvalidArgumentException('This survey response has already been submitted and locked.');
            }

            foreach ($answers as $ans) {
                $questionId = $ans['question_id'];
                $question = EngagementSurveyQuestion::query()->find($questionId);
                if (! $question) {
                    continue;
                }

                $numVal = isset($ans['numeric_value']) ? (float) $ans['numeric_value'] : null;
                $textVal = isset($ans['text_value']) ? $this->privacy->sanitizeFreeText((string) $ans['text_value']) : null;
                $optionId = $ans['option_id'] ?? null;

                $npsCat = null;
                if ($question->question_type === 'nps' && $numVal !== null) {
                    if ($numVal >= 9) {
                        $npsCat = 'promoter';
                    } elseif ($numVal >= 7) {
                        $npsCat = 'passive';
                    } else {
                        $npsCat = 'detractor';
                    }
                }

                $favStatus = null;
                if ($numVal !== null) {
                    if ($question->question_type === 'likert' || $question->question_type === 'rating') {
                        $max = $question->scale_config['max'] ?? 5;
                        $half = $max / 2;
                        if ($numVal >= ($max * 0.75)) {
                            $favStatus = 'favorable';
                        } elseif ($numVal <= ($max * 0.4)) {
                            $favStatus = 'unfavorable';
                        } else {
                            $favStatus = 'neutral';
                        }
                    } elseif ($question->question_type === 'yes_no') {
                        $favStatus = ($numVal == 1) ? 'favorable' : 'unfavorable';
                    }
                }

                EngagementResponseAnswer::query()->create([
                    'tenant_id' => $response->tenant_id,
                    'response_id' => $response->id,
                    'question_id' => $question->id,
                    'option_id' => $optionId,
                    'numeric_value' => $numVal,
                    'text_value' => $textVal,
                    'nps_category' => $npsCat,
                    'favorability_status' => $favStatus,
                ]);
            }

            $response->update([
                'response_status' => 'submitted',
                'submitted_at' => now(),
                'is_locked' => true,
            ]);

            // Burn anonymous token if used
            if ($rawToken && $response->campaign) {
                $this->tokens->burnToken($response->campaign, $rawToken);
            }

            // Mark completion on recipient if identifiable (or passed during session)
            $targetEmployeeId = $response->employee_id ?? $completingEmployee?->id;
            if ($targetEmployeeId) {
                EngagementCampaignRecipient::query()
                    ->where('campaign_id', $response->campaign_id)
                    ->where('employee_id', $targetEmployeeId)
                    ->update(['completed_at' => now()]);
            }

            EngagementResponseSubmitted::dispatch($response);

            return $response->fresh();
        });
    }
}
