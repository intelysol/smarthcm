<?php

namespace App\Domains\SelfService\Models;

use App\Domains\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeePreference extends PortalModel
{
    protected $fillable = ['tenant_id', 'employee_id', 'language', 'theme', 'timezone', 'notification_preferences', 'email_preferences', 'mobile_preferences'];

    protected $casts = ['notification_preferences' => 'array', 'email_preferences' => 'array', 'mobile_preferences' => 'array'];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
