<?php

declare(strict_types=1);

namespace App\Domains\Performance\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompetencyCategory extends Model
{
    use HasUuids;

    protected $fillable = [
        'framework_id',
        'name',
        'description',
        'sort_order',
    ];

    public function framework(): BelongsTo
    {
        return $this->belongsTo(CompetencyFramework::class, 'framework_id');
    }

    public function competencies(): HasMany
    {
        return $this->hasMany(Competency::class, 'category_id');
    }
}
