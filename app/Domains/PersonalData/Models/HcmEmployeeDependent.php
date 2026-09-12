<?php

namespace App\Domains\PersonalData\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmEmployeeDependent extends Model
{
    use HasUuids;

    protected $table = 'hcm_employee_dependents';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'name',
        'first_name',
        'last_name',
        'relationship',
        'date_of_birth',
        'gender',
        'nationality',
        'national_id_number',
        'is_student',
        'is_disabled',
        'contact_phone',
        'address',
        'is_active',
        'effective_from',
        'effective_to',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'is_student' => 'boolean',
        'is_disabled' => 'boolean',
        'is_active' => 'boolean',
        'effective_from' => 'date',
        'effective_to' => 'date',
    ];

    protected $appends = [
        'first_name',
        'last_name',
    ];

    public function getFirstNameAttribute(): ?string
    {
        if (!empty($this->attributes['first_name'])) {
            return $this->attributes['first_name'];
        }
        $parts = explode(' ', $this->attributes['name'] ?? '');
        return $parts[0] ?? '';
    }

    public function getLastNameAttribute(): ?string
    {
        if (!empty($this->attributes['last_name'])) {
            return $this->attributes['last_name'];
        }
        $parts = explode(' ', $this->attributes['name'] ?? '');
        return count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : '';
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
