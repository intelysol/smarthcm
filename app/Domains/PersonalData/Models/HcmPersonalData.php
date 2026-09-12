<?php

namespace App\Domains\PersonalData\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmPersonalData extends Model
{
    use HasUuids;

    protected $table = 'hcm_personal_data';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'legal_first_name',
        'legal_middle_name',
        'legal_last_name',
        'preferred_name',
        'display_name',
        'previous_name',
        'name_prefix',
        'name_suffix',
        'date_of_birth',
        'gender',
        'marital_status',
        'nationality',
        'citizenship',
        'preferred_language',
        'country_of_birth',
        'place_of_birth',
        'personal_email',
        'personal_mobile',
        'personal_phone',
        'photo_path',
        'effective_from',
        'effective_to',
        'is_verified',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'is_verified' => 'boolean',
    ];

    protected $appends = [
        'first_name',
        'last_name',
    ];

    public function getFirstNameAttribute(): ?string
    {
        return $this->attributes['legal_first_name'] ?? null;
    }

    public function getLastNameAttribute(): ?string
    {
        return $this->attributes['legal_last_name'] ?? null;
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function legalFullName(): string
    {
        return trim(collect([$this->legal_first_name, $this->legal_middle_name, $this->legal_last_name])->filter()->implode(' '));
    }
}
