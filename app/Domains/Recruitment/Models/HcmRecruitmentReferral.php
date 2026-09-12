<?php

namespace App\Domains\Recruitment\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmRecruitmentReferral extends Model
{
    use HasUuids;

    protected $table = 'hcm_recruitment_referrals';

    protected $fillable = [
        'tenant_id',
        'requisition_id',
        'candidate_id',
        'referrer_employee_id',
        'status',
        'reward_amount',
        'currency',
    ];

    protected $casts = [
        'reward_amount' => 'decimal:2',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function requisition(): BelongsTo
    {
        return $this->belongsTo(HcmRecruitmentRequisition::class, 'requisition_id');
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(HcmRecruitmentCandidate::class, 'candidate_id');
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'referrer_employee_id');
    }
}
