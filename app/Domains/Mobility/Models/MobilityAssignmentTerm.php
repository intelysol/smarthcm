<?php

namespace App\Domains\Mobility\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MobilityAssignmentTerm extends Model
{
    use HasUuids;

    protected $table = 'hcm_mobility_assignment_terms';

    protected $fillable = [
        'tenant_id',
        'assignment_id',
        'home_employment_terms',
        'host_employment_terms',
        'housing_policy',
        'relocation_policy',
        'travel_policy',
        'expense_policy',
        'tax_treatment',
        'repatriation_terms',
        'additional_terms',
    ];

    protected $casts = [
        'additional_terms' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(MobilityAssignment::class, 'assignment_id');
    }
}
