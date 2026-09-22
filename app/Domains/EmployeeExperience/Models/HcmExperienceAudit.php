<?php

namespace App\Domains\EmployeeExperience\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmExperienceAudit extends Model
{
    use HasUuids;

    protected $table = 'hcm_experience_audits';

    protected $fillable = [
        'tenant_id',
        'actor_user_id',
        'employee_id',
        'action_type',
        'target_type',
        'target_id',
        'metadata',
        'ip_address',
        'user_agent',
        'occurred_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'occurred_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
