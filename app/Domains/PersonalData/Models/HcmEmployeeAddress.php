<?php

namespace App\Domains\PersonalData\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmEmployeeAddress extends Model
{
    use HasUuids;

    protected $table = 'hcm_employee_addresses';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'address_type',
        'address_line_1',
        'address_line_2',
        'city',
        'state_province',
        'postal_code',
        'country_code',
        'district_region',
        'is_current',
        'effective_from',
        'effective_to',
        'verification_status',
        'verified_at',
    ];

    protected $casts = [
        'is_current' => 'boolean',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'verified_at' => 'datetime',
    ];

    protected $appends = [
        'country',
    ];

    public function getCountryAttribute(): ?string
    {
        return $this->attributes['country_code'] ?? null;
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function formattedAddress(): string
    {
        return collect([
            $this->address_line_1,
            $this->address_line_2,
            $this->city,
            $this->state_province,
            $this->postal_code,
            $this->country_code,
        ])->filter()->implode(', ');
    }
}
