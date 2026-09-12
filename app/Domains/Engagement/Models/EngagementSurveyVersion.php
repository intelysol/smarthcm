<?php

namespace App\Domains\Engagement\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EngagementSurveyVersion extends EngagementModel
{
    protected $table = 'engagement_survey_versions';

    protected $fillable = [
        'tenant_id',
        'survey_id',
        'version_number',
        'snapshot',
        'published_at',
        'published_by',
    ];

    protected function casts(): array
    {
        return [
            'version_number' => 'integer',
            'snapshot' => 'array',
            'published_at' => 'datetime',
        ];
    }

    public function survey(): BelongsTo
    {
        return $this->belongsTo(EngagementSurvey::class, 'survey_id');
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }
}
