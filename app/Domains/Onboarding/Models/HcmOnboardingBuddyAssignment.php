<?php

namespace App\Domains\Onboarding\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmOnboardingBuddyAssignment extends Model
{
    use HasUuids;

    protected $table = 'hcm_onboarding_buddy_assignments';

    protected $fillable = [
        'tenant_id',
        'case_id',
        'employee_id',
        'buddy_employee_id',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function case(): BelongsTo
    {
        return $this->belongsTo(HcmOnboardingCase::class, 'case_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function buddy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'buddy_employee_id');
    }
}
