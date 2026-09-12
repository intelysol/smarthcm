<?php

declare(strict_types=1);

namespace App\Domains\Performance\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Competency extends PerformanceModel
{
    protected $fillable = [
        'tenant_id',
        'category_id',
        'name',
        'description',
        'status',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(CompetencyCategory::class, 'category_id');
    }

    public function levels(): HasMany
    {
        return $this->hasMany(CompetencyLevel::class, 'competency_id');
    }
}
