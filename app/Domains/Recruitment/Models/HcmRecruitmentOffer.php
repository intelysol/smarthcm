<?php

namespace App\Domains\Recruitment\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class HcmRecruitmentOffer extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'hcm_recruitment_offers';

    protected $fillable = [
        'tenant_id',
        'offer_number',
        'application_id',
        'candidate_id',
        'requisition_id',
        'current_version',
        'base_salary',
        'bonus_amount',
        'currency',
        'start_date',
        'expiry_date',
        'employment_type',
        'benefits_summary',
        'status',
        'approved_at',
        'sent_at',
        'accepted_at',
        'rejected_at',
        'decline_reason',
    ];

    protected $casts = [
        'current_version' => 'integer',
        'base_salary' => 'decimal:2',
        'bonus_amount' => 'decimal:2',
        'start_date' => 'date',
        'expiry_date' => 'date',
        'benefits_summary' => 'array',
        'approved_at' => 'datetime',
        'sent_at' => 'datetime',
        'accepted_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(HcmRecruitmentApplication::class, 'application_id');
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(HcmRecruitmentCandidate::class, 'candidate_id');
    }

    public function requisition(): BelongsTo
    {
        return $this->belongsTo(HcmRecruitmentRequisition::class, 'requisition_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(HcmRecruitmentOfferVersion::class, 'offer_id');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(HcmRecruitmentOfferApproval::class, 'offer_id');
    }
}
