<?php

namespace App\Domains\Recruitment\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmRecruitmentHiringDecision extends Model
{
    use HasUuids;

    protected $table = 'hcm_recruitment_hiring_decisions';

    protected $fillable = [
        'tenant_id',
        'application_id',
        'candidate_id',
        'requisition_id',
        'decision_maker_id',
        'decision',
        'final_start_date',
        'decision_rationale',
        'core_hr_employee_id',
        'handoff_status',
        'handed_off_at',
    ];

    protected $casts = [
        'final_start_date' => 'date',
        'handed_off_at' => 'datetime',
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

    public function decisionMaker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decision_maker_id');
    }

    public function coreHrEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'core_hr_employee_id');
    }
}
