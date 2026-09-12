<?php

namespace App\Domains\Employee\Models;

class EmployeeEducation extends EmployeeChildModel
{
    protected $fillable = ['tenant_id', 'employee_id', 'degree', 'institution', 'board_university', 'major', 'start_date', 'end_date', 'grade', 'certificate_path', 'created_by', 'updated_by', 'deleted_by'];
    protected $casts = ['start_date' => 'date', 'end_date' => 'date'];
}
