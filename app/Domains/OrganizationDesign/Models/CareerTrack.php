<?php

namespace App\Domains\OrganizationDesign\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CareerTrack extends Model
{
    use HasUuids;

    protected $table = 'career_tracks';

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'track_type',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function careerLevels(): HasMany
    {
        return $this->hasMany(CareerLevel::class, 'career_track_id')->orderBy('rank_order');
    }

    public function jobProfiles(): HasMany
    {
        return $this->hasMany(JobProfile::class, 'career_track_id');
    }
}
