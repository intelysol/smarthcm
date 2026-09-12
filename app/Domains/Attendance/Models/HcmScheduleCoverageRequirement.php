<?php

namespace App\Domains\Attendance\Models;

use App\Domains\Career\Models\CareerSkill;
use App\Domains\Organization\Models\Department;
use App\Domains\Organization\Models\Position;
use App\Domains\Organization\Models\WorkLocation;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmScheduleCoverageRequirement extends Model
{
    use HasUuids;

    protected $table = 'hcm_schedule_coverage_requirements';

    protected $fillable = [
        'tenant_id',
        'roster_period_id',
        'requirement_date',
        'shift_definition_id',
        'start_time',
        'end_time',
        'required_headcount',
        'department_id',
        'location_id',
        'position_id',
        'required_skill_id',
        'min_proficiency_level',
        'capacity_calculation_id',
    ];

    protected $casts = [
        'requirement_date' => 'date',
        'required_headcount' => 'integer',
        'min_proficiency_level' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function rosterPeriod(): BelongsTo
    {
        return $this->belongsTo(RosterPeriod::class, 'roster_period_id');
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(ShiftDefinition::class, 'shift_definition_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(WorkLocation::class, 'location_id');
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function requiredSkill(): BelongsTo
    {
        return $this->belongsTo(CareerSkill::class, 'required_skill_id');
    }
}
