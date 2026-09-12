<?php

namespace App\Domains\OrganizationDesign\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobProfileVersion extends Model
{
    use HasUuids;

    protected $table = 'job_profile_versions';

    protected $fillable = [
        'tenant_id',
        'job_profile_id',
        'version_number',
        'snapshot_data',
        'change_summary',
        'created_by_user_id',
        'approved_by_user_id',
    ];

    protected $casts = [
        'version_number' => 'integer',
        'snapshot_data' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(JobProfile::class, 'job_profile_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }
}
