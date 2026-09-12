<?php

namespace App\Domains\WorkforceProductivity\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmProductivityTimeRecord extends Model
{
    use HasUuids;

    protected $table = 'hcm_productivity_time_records';

    protected $fillable = [
        'tenant_id',
        'record_date',
        'employee_id',
        'department_id',
        'shift_id',
        'category',
        'nature',
        'minutes',
        'hours',
        'source_domain',
        'source_record_id',
        'metadata',
    ];

    protected $casts = [
        'record_date' => 'date',
        'minutes' => 'integer',
        'hours' => 'decimal:2',
        'metadata' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
}
