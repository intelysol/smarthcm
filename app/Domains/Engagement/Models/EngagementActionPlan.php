<?php

namespace App\Domains\Engagement\Models;

use App\Domains\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EngagementActionPlan extends EngagementModel
{
    use SoftDeletes;

    protected $table = 'engagement_action_plans';

    protected $fillable = [
        'tenant_id',
        'campaign_id',
        'title',
        'description',
        'scope_type',
        'scope_id',
        'owner_id',
        'due_date',
        'priority',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(EngagementCampaign::class, 'campaign_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'owner_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(EngagementActionItem::class, 'action_plan_id');
    }
}
