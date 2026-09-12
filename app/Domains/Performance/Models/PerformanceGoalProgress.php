<?php

namespace App\Domains\Performance\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceGoalProgress extends PerformanceModel
{
    protected $fillable = ['goal_id', 'recorded_by', 'recorded_at', 'previous_value', 'new_value', 'progress_percentage', 'comment', 'source'];
    protected function casts(): array { return ['recorded_at' => 'datetime', 'previous_value' => 'decimal:4', 'new_value' => 'decimal:4', 'progress_percentage' => 'decimal:2']; }
    public function goal(): BelongsTo { return $this->belongsTo(PerformanceGoal::class, 'goal_id'); }
}
