<?php

namespace App\Domains\Performance\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceCompetencyAssessment extends Model
{
    use HasUuids;

    protected $fillable = ['review_id', 'competency_id', 'rating', 'comment', 'evidence'];
    protected function casts(): array { return ['rating' => 'decimal:4']; }

    public function review(): BelongsTo
    {
        return $this->belongsTo(PerformanceReview::class, 'review_id');
    }

    public function competency(): BelongsTo
    {
        return $this->belongsTo(Competency::class, 'competency_id');
    }
}
