<?php

namespace App\Domains\PersonalData\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmEmployeeIdentifier extends Model
{
    use HasUuids;

    protected $table = 'hcm_employee_identifiers';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'identifier_type',
        'identifier_value',
        'masked_value',
        'country_code',
        'issuing_authority',
        'issue_date',
        'expiry_date',
        'verification_status',
        'verified_at',
        'is_primary',
    ];

    protected $casts = [
        'identifier_value' => 'encrypted',
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'verified_at' => 'datetime',
        'is_primary' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
