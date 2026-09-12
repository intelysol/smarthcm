<?php

namespace App\Domains\Engagement\Models;

class EngagementSurveyTemplate extends EngagementModel
{
    protected $table = 'engagement_survey_templates';

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'description',
        'survey_type',
        'structure',
        'is_system',
    ];

    protected function casts(): array
    {
        return [
            'structure' => 'array',
            'is_system' => 'boolean',
        ];
    }
}
