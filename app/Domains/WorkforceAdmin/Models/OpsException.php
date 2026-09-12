<?php

namespace App\Domains\WorkforceAdmin\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use App\Domains\WorkforceAdmin\Enums\ExceptionStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OpsException extends Model
{
    use HasUuids;

    protected $table = 'hcm_ops_exceptions';

    protected $fillable = [
        'tenant_id',
        'exception_number',
        'exception_type',
        'severity',
        'domain',
        'entity_type',
        'entity_id',
        'employee_id',
        'description',
        'status',
        'owner_id',
        'assigned_team',
        'detected_at',
        'resolution_guidance',
        'resolution_notes',
        'resolved_at',
        'resolved_by',
    ];

    protected $casts = [
        'status' => ExceptionStatus::class,
        'detected_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(OpsExceptionAssignment::class, 'exception_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(OpsExceptionEvent::class, 'exception_id');
    }
}
