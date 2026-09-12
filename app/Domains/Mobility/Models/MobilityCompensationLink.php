<?php

namespace App\Domains\Mobility\Models;

use App\Domains\Compensation\Models\HcmCompRecommendation;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MobilityCompensationLink extends Model
{
    use HasUuids;

    protected $table = 'hcm_mobility_compensation_links';

    protected $fillable = [
        'tenant_id',
        'assignment_id',
        'compensation_recommendation_id',
        'home_salary',
        'host_salary',
        'mobility_allowance',
        'cost_of_living_allowance',
        'housing_allowance',
        'hardship_allowance',
        'currency',
    ];

    protected $casts = [
        'home_salary' => 'decimal:4',
        'host_salary' => 'decimal:4',
        'mobility_allowance' => 'decimal:4',
        'cost_of_living_allowance' => 'decimal:4',
        'housing_allowance' => 'decimal:4',
        'hardship_allowance' => 'decimal:4',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(MobilityAssignment::class, 'assignment_id');
    }

    public function compensationRecommendation(): BelongsTo
    {
        return $this->belongsTo(HcmCompRecommendation::class, 'compensation_recommendation_id');
    }
}
