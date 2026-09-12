<?php

namespace App\Domains\Attendance\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmEmployeeShiftPreference extends Model
{
    use HasUuids;

    protected $table = 'hcm_employee_shift_preferences';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'shift_definition_id',
        'day_of_week',
        'preference_type',
        'priority',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
        'priority' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(ShiftDefinition::class, 'shift_definition_id');
    }
}
