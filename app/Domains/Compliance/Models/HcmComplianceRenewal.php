<?php

namespace App\Domains\Compliance\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class HcmComplianceRenewal extends Model
{
    use HasUuids;

    protected $table = 'hcm_compliance_renewals';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'renewable_type',
        'renewable_id',
        'status',
        'old_expiry_date',
        'new_expiry_date',
        'initiated_at',
        'completed_at',
        'initiated_by',
        'notes',
    ];

    protected $casts = [
        'old_expiry_date' => 'date',
        'new_expiry_date' => 'date',
        'initiated_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    public function renewable(): MorphTo
    {
        return $this->morphTo();
    }
}
