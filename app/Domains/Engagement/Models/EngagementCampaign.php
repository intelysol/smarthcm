<?php

namespace App\Domains\Engagement\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class EngagementCampaign extends EngagementModel
{
    use SoftDeletes;

    protected $table = 'engagement_campaigns';

    protected $fillable = [
        'tenant_id',
        'survey_id',
        'survey_version_id',
        'code',
        'name',
        'description',
        'start_date',
        'end_date',
        'timezone',
        'status',
        'minimum_response_threshold',
        'is_recurring',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'minimum_response_threshold' => 'integer',
            'is_recurring' => 'boolean',
        ];
    }

    public function survey(): BelongsTo
    {
        return $this->belongsTo(EngagementSurvey::class, 'survey_id');
    }

    public function surveyVersion(): BelongsTo
    {
        return $this->belongsTo(EngagementSurveyVersion::class, 'survey_version_id');
    }

    public function schedule(): HasOne
    {
        return $this->hasOne(EngagementCampaignSchedule::class, 'campaign_id');
    }

    public function audiences(): HasMany
    {
        return $this->hasMany(EngagementSurveyAudience::class, 'campaign_id');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(EngagementCampaignRecipient::class, 'campaign_id');
    }

    public function tokens(): HasMany
    {
        return $this->hasMany(EngagementParticipationToken::class, 'campaign_id');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(EngagementResponse::class, 'campaign_id');
    }

    public function actionPlans(): HasMany
    {
        return $this->hasMany(EngagementActionPlan::class, 'campaign_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
