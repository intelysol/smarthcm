<?php

namespace App\Domains\OrganizationDesign\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobFamily extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'job_families';

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'description',
        'status',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function subFamilies(): HasMany
    {
        return $this->hasMany(JobSubFamily::class, 'job_family_id');
    }

    public function jobProfiles(): HasMany
    {
        return $this->hasMany(JobProfile::class, 'job_family_id');
    }
}
