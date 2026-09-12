<?php

namespace App\Domains\OrganizationDesign\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobSubFamily extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'job_sub_families';

    protected $fillable = [
        'tenant_id',
        'job_family_id',
        'code',
        'name',
        'description',
        'status',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(JobFamily::class, 'job_family_id');
    }

    public function jobProfiles(): HasMany
    {
        return $this->hasMany(JobProfile::class, 'job_sub_family_id');
    }
}
