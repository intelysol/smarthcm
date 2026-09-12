<?php

namespace App\Domains\Engagement\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EngagementSurveySection extends EngagementModel
{
    protected $table = 'engagement_survey_sections';

    protected $fillable = [
        'tenant_id',
        'survey_id',
        'title',
        'description',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function survey(): BelongsTo
    {
        return $this->belongsTo(EngagementSurvey::class, 'survey_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(EngagementSurveyQuestion::class, 'section_id')->orderBy('sort_order');
    }
}
