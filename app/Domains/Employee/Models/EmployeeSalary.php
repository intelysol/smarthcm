<?php

namespace App\Domains\Employee\Models;

class EmployeeSalary extends EmployeeChildModel
{
    protected $fillable = ['tenant_id', 'employee_id', 'salary_structure', 'basic_salary', 'gross_salary', 'currency', 'payroll_group', 'created_by', 'updated_by', 'deleted_by'];
    protected $casts = ['basic_salary' => 'decimal:2', 'gross_salary' => 'decimal:2'];
}
