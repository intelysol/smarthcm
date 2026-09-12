<?php

namespace App\Domains\Learning\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearningDevelopmentActivity extends Model
{
    use HasUuids;

    protected $table = 'hcm_learning_development_activities';

    protected $fillable = [
        'tenant_id',
        'development_plan_id',
        'activity_type',
        'title',
        'description',
        'course_id',
        'target_date',
        'status',
        'completed_at',
        'evidence_notes',
    ];

    protected $casts = [
        'target_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function developmentPlan(): BelongsTo
    {
        return $this->belongsTo(LearningDevelopmentPlan::class, 'development_plan_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(LearningCourse::class, 'course_id');
    }
}
