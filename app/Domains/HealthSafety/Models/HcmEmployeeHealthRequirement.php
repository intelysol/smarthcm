<?php

namespace App\Domains\HealthSafety\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmEmployeeHealthRequirement extends Model
{
    use HasUuids;

    protected $table = 'hcm_employee_health_requirements';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'health_requirement_id',
        'status',
        'due_date',
        'completed_date',
        'expiry_date',
        'notes',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_date' => 'date',
        'expiry_date' => 'date',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function requirement(): BelongsTo
    {
        return $this->belongsTo(HcmHealthRequirement::class, 'health_requirement_id');
    }

    public function healthRequirement(): BelongsTo
    {
        return $this->requirement();
    }
}
