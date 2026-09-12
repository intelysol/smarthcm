<?php

namespace App\Domains\Recruitment\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class HcmRecruitmentApplication extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'hcm_recruitment_applications';

    protected $fillable = [
        'tenant_id',
        'application_number',
        'candidate_id',
        'requisition_id',
        'stage_id',
        'status',
        'source_id',
        'cover_letter',
        'screening_score',
        'screening_notes',
        'rejection_reason',
        'applied_at',
        'withdrawn_at',
        'rejected_at',
        'hired_at',
    ];

    protected $casts = [
        'screening_score' => 'decimal:2',
        'applied_at' => 'datetime',
        'withdrawn_at' => 'datetime',
        'rejected_at' => 'datetime',
        'hired_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(HcmRecruitmentCandidate::class, 'candidate_id');
    }

    public function requisition(): BelongsTo
    {
        return $this->belongsTo(HcmRecruitmentRequisition::class, 'requisition_id');
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(HcmRecruitmentApplicationStage::class, 'stage_id');
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(HcmRecruitmentSource::class, 'source_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(HcmRecruitmentApplicationActivity::class, 'application_id');
    }

    public function screenings(): HasMany
    {
        return $this->hasMany(HcmRecruitmentScreening::class, 'application_id');
    }

    public function interviews(): HasMany
    {
        return $this->hasMany(HcmRecruitmentInterview::class, 'application_id');
    }

    public function offer(): HasOne
    {
        return $this->hasOne(HcmRecruitmentOffer::class, 'application_id');
    }

    public function backgroundChecks(): HasMany
    {
        return $this->hasMany(HcmRecruitmentBackgroundCheck::class, 'application_id');
    }

    public function hiringDecision(): HasOne
    {
        return $this->hasOne(HcmRecruitmentHiringDecision::class, 'application_id');
    }
}
