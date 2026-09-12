<?php

declare(strict_types=1);

namespace App\Domains\Performance\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceSelfAssessment extends PerformanceModel
{
    protected $fillable = [
        'tenant_id',
        'review_id',
        'goal_assessment',
        'competency_assessment',
        'achievements',
        'challenges',
        'development',
        'overall_self_rating',
        'comments',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'goal_assessment' => 'array',
            'competency_assessment' => 'array',
            'overall_self_rating' => 'decimal:4',
            'submitted_at' => 'datetime',
        ];
    }

    public function review(): BelongsTo
    {
        return $this->belongsTo(PerformanceReview::class, 'review_id');
    }
}
