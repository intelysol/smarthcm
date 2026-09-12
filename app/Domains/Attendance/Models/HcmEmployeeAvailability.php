<?php

namespace App\Domains\Attendance\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmEmployeeAvailability extends Model
{
    use HasUuids;

    protected $table = 'hcm_employee_availabilities';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'date',
        'start_time',
        'end_time',
        'availability_type',
        'reason',
        'is_recurring',
        'recurring_day_of_week',
    ];

    protected $casts = [
        'date' => 'date',
        'is_recurring' => 'boolean',
        'recurring_day_of_week' => 'integer',
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
