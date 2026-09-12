<?php

namespace App\Domains\Employee\Models;

class EmployeeBankAccount extends EmployeeChildModel
{
    protected $fillable = ['tenant_id', 'employee_id', 'bank', 'branch', 'iban', 'account_number', 'account_title', 'is_primary', 'created_by', 'updated_by', 'deleted_by'];
    protected $casts = ['is_primary' => 'boolean'];
}
