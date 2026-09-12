<?php

namespace App\Domains\Performance\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class PerformanceRatingScale extends PerformanceModel
{
    protected $fillable = ['tenant_id', 'name', 'rating_type', 'is_default'];
    protected function casts(): array { return ['is_default' => 'boolean']; }
    public function items(): HasMany { return $this->hasMany(PerformanceRatingScaleItem::class, 'rating_scale_id'); }
}
