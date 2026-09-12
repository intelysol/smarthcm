<?php

namespace App\Domains\Employee\Models;

class EmployeeLanguage extends EmployeeChildModel
{
    protected $fillable = ['tenant_id', 'employee_id', 'language', 'reading_level', 'writing_level', 'speaking_level', 'is_native', 'created_by', 'updated_by', 'deleted_by'];
    protected $casts = ['is_native' => 'boolean'];
}
