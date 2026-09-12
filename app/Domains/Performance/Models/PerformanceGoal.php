<?php

namespace App\Domains\Performance\Models;

use App\Domains\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;

class PerformanceGoal extends PerformanceModel
{
    use SoftDeletes;
    protected $fillable = ['tenant_id', 'cycle_id', 'employee_id', 'parent_goal_id', 'owner_type', 'title', 'description', 'goal_type', 'measurement_type', 'weight', 'target_value', 'current_value', 'unit', 'start_date', 'due_date', 'status', 'progress_percentage', 'priority', 'version', 'created_by', 'updated_by'];
    protected function casts(): array { return ['weight' => 'decimal:2', 'target_value' => 'decimal:4', 'current_value' => 'decimal:4', 'progress_percentage' => 'decimal:2', 'start_date' => 'date', 'due_date' => 'date', 'version' => 'integer']; }
    protected static function booted(): void { static::creating(function (self $goal): void { $goal->uuid ??= (string) Str::uuid(); }); }
    public function cycle(): BelongsTo { return $this->belongsTo(PerformanceCycle::class, 'cycle_id'); }
    public function employee(): BelongsTo { return $this->belongsTo(Employee::class, 'employee_id'); }
    public function parent(): BelongsTo { return $this->belongsTo(self::class, 'parent_goal_id'); }
    public function children(): HasMany { return $this->hasMany(self::class, 'parent_goal_id'); }
    public function progressRecords(): HasMany { return $this->hasMany(PerformanceGoalProgress::class, 'goal_id'); }
    public function milestones(): HasMany { return $this->hasMany(PerformanceGoalMilestone::class, 'goal_id'); }
}
