<?php

namespace App\Domains\OrganizationDesign\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobLevel extends Model
{
    use HasUuids;

    protected $table = 'job_levels';

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'numerical_level',
        'description',
        'is_active',
    ];

    protected $casts = [
        'numerical_level' => 'integer',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function jobProfiles(): HasMany
    {
        return $this->hasMany(JobProfile::class, 'job_level_id');
    }
}
