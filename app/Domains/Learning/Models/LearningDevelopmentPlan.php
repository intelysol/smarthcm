<?php

namespace App\Domains\Learning\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LearningDevelopmentPlan extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'hcm_learning_development_plans';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'title',
        'goal',
        'skill_target',
        'competency_target',
        'target_level',
        'target_completion_date',
        'status',
        'manager_id',
        'mentor_id',
        'manager_notes',
        'completed_at',
    ];

    protected $casts = [
        'target_completion_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    public function mentor(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'mentor_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(LearningDevelopmentActivity::class, 'development_plan_id');
    }
}
