<?php

namespace App\Domains\Offboarding\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SeparationHandoverRecord extends Model
{
    use HasUuids;

    protected $table = 'separation_handover_records';

    protected $fillable = [
        'tenant_id',
        'separation_request_id',
        'successor_employee_id',
        'handover_date',
        'status',
        'handover_notes',
        'manager_verified_by',
        'manager_verified_at',
    ];

    protected $casts = [
        'handover_date' => 'date',
        'manager_verified_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(SeparationRequest::class, 'separation_request_id');
    }

    public function successor(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'successor_employee_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_verified_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SeparationHandoverItem::class, 'separation_handover_record_id');
    }
}
