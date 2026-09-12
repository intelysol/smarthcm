<?php

namespace App\Domains\Employee\Models;

class EmployeeWorkExperience extends EmployeeChildModel
{
    protected $fillable = ['tenant_id', 'employee_id', 'company', 'position', 'industry', 'start_date', 'end_date', 'responsibilities', 'salary', 'reason_for_leaving', 'created_by', 'updated_by', 'deleted_by'];
    protected $casts = ['start_date' => 'date', 'end_date' => 'date', 'salary' => 'decimal:2'];
}
