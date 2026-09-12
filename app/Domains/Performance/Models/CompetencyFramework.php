<?php

declare(strict_types=1);

namespace App\Domains\Performance\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class CompetencyFramework extends PerformanceModel
{
    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'status',
    ];

    public function categories(): HasMany
    {
        return $this->hasMany(CompetencyCategory::class, 'framework_id');
    }
}
