<?php

namespace App\Domains\Mobility\Models;

use App\Domains\Benefits\Models\HcmBenefitElection;
use App\Domains\Benefits\Models\HcmBenefitPlan;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MobilityBenefitLink extends Model
{
    use HasUuids;

    protected $table = 'hcm_mobility_benefit_links';

    protected $fillable = [
        'tenant_id',
        'assignment_id',
        'benefit_plan_id',
        'benefit_election_id',
        'benefit_category',
        'status',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(MobilityAssignment::class, 'assignment_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(HcmBenefitPlan::class, 'benefit_plan_id');
    }

    public function election(): BelongsTo
    {
        return $this->belongsTo(HcmBenefitElection::class, 'benefit_election_id');
    }
}
