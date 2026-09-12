<?php

namespace App\Domains\PersonalData\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmEmergencyContact extends Model
{
    use HasUuids;

    protected $table = 'hcm_emergency_contacts';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'contact_name',
        'relationship',
        'priority',
        'is_primary',
        'mobile',
        'telephone',
        'email',
        'address',
        'country',
        'preferred_communication_method',
        'is_active',
        'effective_from',
        'effective_to',
    ];

    protected $casts = [
        'priority' => 'integer',
        'is_primary' => 'boolean',
        'is_active' => 'boolean',
        'effective_from' => 'date',
        'effective_to' => 'date',
    ];

    protected $appends = [
        'name',
        'primary_phone',
        'secondary_phone',
        'priority_order',
    ];

    public function getNameAttribute(): ?string
    {
        return $this->attributes['contact_name'] ?? null;
    }

    public function getPrimaryPhoneAttribute(): ?string
    {
        return $this->attributes['mobile'] ?? null;
    }

    public function getSecondaryPhoneAttribute(): ?string
    {
        return $this->attributes['telephone'] ?? null;
    }

    public function getPriorityOrderAttribute(): ?int
    {
        return $this->attributes['priority'] ?? null;
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
