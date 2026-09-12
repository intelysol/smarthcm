<?php

namespace App\Domains\Performance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PerformanceGoalMilestone extends Model
{
    use HasUuids;
    protected $fillable = ['goal_id', 'title', 'description', 'due_date', 'weight', 'status', 'completed_at'];
    protected function casts(): array { return ['due_date' => 'date', 'completed_at' => 'datetime', 'weight' => 'decimal:2']; }
}
