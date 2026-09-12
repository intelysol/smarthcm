<?php

namespace App\Domains\Performance\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceCycleConfiguration extends PerformanceModel
{
    protected $fillable = ['tenant_id', 'cycle_id', 'goal_weight', 'competency_weight', 'feedback_weight', 'self_assessment_enabled', 'manager_assessment_enabled', 'feedback_360_enabled', 'calibration_enabled', 'pip_enabled', 'employee_acknowledgement_required', 'minimum_response_count', 'eligibility_rules', 'feedback_groups', 'formula_components'];
    protected function casts(): array { return ['goal_weight' => 'decimal:2', 'competency_weight' => 'decimal:2', 'feedback_weight' => 'decimal:2', 'self_assessment_enabled' => 'boolean', 'manager_assessment_enabled' => 'boolean', 'feedback_360_enabled' => 'boolean', 'calibration_enabled' => 'boolean', 'pip_enabled' => 'boolean', 'employee_acknowledgement_required' => 'boolean', 'eligibility_rules' => 'array', 'feedback_groups' => 'array', 'formula_components' => 'array']; }
    public function cycle(): BelongsTo { return $this->belongsTo(PerformanceCycle::class, 'cycle_id'); }
}
