<?php

namespace App\Domains\Lifecycle\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonnelActionAcknowledgement extends Model
{
    use HasUuids;

    protected $table = 'personnel_action_acknowledgements';

    protected $fillable = [
        'tenant_id',
        'personnel_action_request_id',
        'employee_id',
        'status',
        'acknowledged_at',
        'comment',
        'ip_address',
    ];

    protected $casts = [
        'acknowledged_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(PersonnelActionRequest::class, 'personnel_action_request_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
