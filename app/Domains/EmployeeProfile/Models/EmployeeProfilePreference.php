<?php

namespace App\Domains\EmployeeProfile\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeProfilePreference extends Model
{
    use HasUuids;

    protected $table = 'employee_profile_preferences';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'show_personal_email',
        'show_personal_phone',
        'show_birthday',
        'theme_preference',
        'custom_settings',
    ];

    protected $casts = [
        'show_personal_email' => 'boolean',
        'show_personal_phone' => 'boolean',
        'show_birthday' => 'boolean',
        'custom_settings' => 'array',
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
