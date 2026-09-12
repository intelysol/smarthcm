<?php

namespace App\Domains\Engagement\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Department;
use App\Domains\Organization\Models\Location;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EngagementResponse extends EngagementModel
{
    protected $table = 'engagement_responses';

    protected $fillable = [
        'tenant_id',
        'survey_id',
        'survey_version_id',
        'campaign_id',
        'employee_id',
        'confidentiality_type',
        'response_status',
        'started_at',
        'submitted_at',
        'is_locked',
        'department_id',
        'location_id',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'submitted_at' => 'datetime',
            'is_locked' => 'boolean',
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

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(EngagementCampaign::class, 'campaign_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(EngagementResponseAnswer::class, 'response_id');
    }
}
