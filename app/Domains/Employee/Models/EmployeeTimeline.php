<?php

namespace App\Domains\Employee\Models;

class EmployeeTimeline extends EmployeeChildModel
{
    protected $fillable = ['tenant_id', 'employee_id', 'event_type', 'title', 'description', 'old_values', 'new_values', 'occurred_at', 'created_by', 'updated_by', 'deleted_by'];
    protected $casts = ['old_values' => 'array', 'new_values' => 'array', 'occurred_at' => 'datetime'];
}
