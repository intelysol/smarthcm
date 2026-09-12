<?php

namespace App\Domains\Recruitment\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmRecruitmentJobPosting extends Model
{
    use HasUuids;

    protected $table = 'hcm_recruitment_job_postings';

    protected $fillable = [
        'tenant_id',
        'requisition_id',
        'title',
        'slug',
        'summary',
        'description',
        'posting_type',
        'location_display',
        'is_remote',
        'status',
        'published_at',
        'closes_at',
    ];

    protected $casts = [
        'is_remote' => 'boolean',
        'published_at' => 'datetime',
        'closes_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function requisition(): BelongsTo
    {
        return $this->belongsTo(HcmRecruitmentRequisition::class, 'requisition_id');
    }
}
