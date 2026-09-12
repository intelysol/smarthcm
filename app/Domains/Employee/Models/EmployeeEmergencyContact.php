<?php

namespace App\Domains\Employee\Models;

class EmployeeEmergencyContact extends EmployeeChildModel
{
    protected $fillable = ['tenant_id', 'employee_id', 'name', 'relationship', 'phone', 'mobile', 'email', 'address', 'priority', 'created_by', 'updated_by', 'deleted_by'];
    protected $casts = ['priority' => 'integer'];
}
