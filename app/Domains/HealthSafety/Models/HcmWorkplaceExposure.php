<?php

namespace App\Domains\HealthSafety\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmWorkplaceExposure extends Model
{
    use HasUuids;

    protected $table = 'hcm_workplace_exposures';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'hazard_type',
        'hazard_name',
        'exposure_level',
        'effective_from',
        'effective_to',
        'surveillance_required',
        'surveillance_interval_months',
        'controls_in_place',
        'status',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_to' => 'date',
        'surveillance_required' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
