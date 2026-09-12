<?php

namespace App\Domains\Performance\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;

class PerformanceReview extends PerformanceModel
{
    use SoftDeletes;
    protected $fillable = ['tenant_id', 'cycle_id', 'employee_id', 'reviewer_id', 'review_type', 'status', 'overall_rating', 'calculated_rating', 'final_rating', 'summary', 'submitted_at', 'finalized_at', 'version'];
    protected function casts(): array { return ['overall_rating' => 'decimal:4', 'calculated_rating' => 'decimal:4', 'final_rating' => 'decimal:4', 'submitted_at' => 'datetime', 'finalized_at' => 'datetime', 'version' => 'integer']; }
    protected static function booted(): void { static::creating(function (self $review): void { $review->uuid ??= (string) Str::uuid(); }); }
    public function competencyAssessments(): HasMany { return $this->hasMany(PerformanceCompetencyAssessment::class, 'review_id'); }
}
