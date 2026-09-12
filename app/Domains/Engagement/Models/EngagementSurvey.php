<?php

namespace App\Domains\Engagement\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EngagementSurvey extends EngagementModel
{
    use SoftDeletes;

    protected $table = 'engagement_surveys';

    protected $fillable = [
        'tenant_id',
        'code',
        'title',
        'description',
        'survey_type',
        'instructions',
        'confidentiality_type',
        'allow_multiple_responses',
        'start_date',
        'end_date',
        'status',
        'version',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'allow_multiple_responses' => 'boolean',
            'start_date' => 'date',
            'end_date' => 'date',
            'version' => 'integer',
        ];
    }

    public function sections(): HasMany
    {
        return $this->hasMany(EngagementSurveySection::class, 'survey_id')->orderBy('sort_order');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(EngagementSurveyQuestion::class, 'survey_id')->orderBy('sort_order');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(EngagementSurveyVersion::class, 'survey_id')->orderByDesc('version_number');
    }

    public function rules(): HasMany
    {
        return $this->hasMany(EngagementQuestionRule::class, 'survey_id');
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(EngagementCampaign::class, 'survey_id');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(EngagementResponse::class, 'survey_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
