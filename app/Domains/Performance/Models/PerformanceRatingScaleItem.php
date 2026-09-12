<?php

namespace App\Domains\Performance\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PerformanceRatingScaleItem extends Model
{
    use HasUuids;
    protected $fillable = ['rating_scale_id', 'value', 'label', 'description', 'min_score', 'max_score'];
    protected function casts(): array { return ['value' => 'decimal:4', 'min_score' => 'decimal:4', 'max_score' => 'decimal:4']; }
}
