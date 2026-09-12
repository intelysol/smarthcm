<?php

namespace App\Domains\Mobility\Models;

use App\Domains\Organization\Models\WorkLocation;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MobilityAssignmentLocation extends Model
{
    use HasUuids;

    protected $table = 'hcm_mobility_assignment_locations';

    protected $fillable = [
        'tenant_id',
        'assignment_id',
        'location_type',
        'country',
        'city',
        'state_province',
        'address',
        'work_location_id',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(MobilityAssignment::class, 'assignment_id');
    }

    public function workLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'work_location_id');
    }
}
