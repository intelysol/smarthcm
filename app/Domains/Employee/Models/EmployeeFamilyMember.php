<?php

namespace App\Domains\Employee\Models;

class EmployeeFamilyMember extends EmployeeChildModel
{
    protected $fillable = ['tenant_id', 'employee_id', 'name', 'date_of_birth', 'relationship', 'occupation', 'created_by', 'updated_by', 'deleted_by'];
    protected $casts = ['date_of_birth' => 'date'];
}
