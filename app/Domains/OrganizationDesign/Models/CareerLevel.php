<?php

namespace App\Domains\OrganizationDesign\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CareerLevel extends Model
{
    use HasUuids;

    protected $table = 'career_levels';

    protected $fillable = [
        'tenant_id',
        'career_track_id',
        'level_code',
        'name',
        'rank_order',
        'typical_experience_years',
        'scope_description',
    ];

    protected $casts = [
        'rank_order' => 'integer',
        'typical_experience_years' => 'decimal:1',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function track(): BelongsTo
    {
        return $this->belongsTo(CareerTrack::class, 'career_track_id');
    }

    public function jobProfiles(): HasMany
    {
        return $this->hasMany(JobProfile::class, 'career_level_id');
    }
}
