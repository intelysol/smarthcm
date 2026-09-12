<?php

namespace App\Domains\HealthSafety\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmReturnToWorkCase extends Model
{
    use HasUuids;

    protected $table = 'hcm_return_to_work_cases';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'case_number',
        'absence_reason',
        'incident_or_absence_date',
        'target_return_date',
        'actual_return_date',
        'return_phase',
        'status',
        'case_manager_id',
        'plan_summary',
        'clearance_document_id',
    ];

    protected $casts = [
        'incident_or_absence_date' => 'date',
        'target_return_date' => 'date',
        'actual_return_date' => 'date',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function caseManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'case_manager_id');
    }
}
