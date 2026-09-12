<?php

namespace App\Domains\Performance\Models;

class PerformanceFinalOutcome extends PerformanceModel
{
    protected $fillable = ['tenant_id', 'cycle_id', 'employee_id', 'final_rating', 'final_score', 'summary', 'strengths', 'development_areas', 'finalized_by', 'finalized_at', 'employee_acknowledged_at', 'employee_comment', 'acknowledgement_status'];
    protected function casts(): array { return ['final_rating' => 'decimal:4', 'final_score' => 'decimal:4', 'finalized_at' => 'datetime', 'employee_acknowledged_at' => 'datetime']; }
}
