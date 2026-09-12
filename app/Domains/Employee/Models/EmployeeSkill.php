<?php

namespace App\Domains\Employee\Models;

class EmployeeSkill extends EmployeeChildModel
{
    protected $table = 'employee_profile_skills';
    protected $fillable = ['tenant_id', 'employee_id', 'skill_name', 'skill_type', 'skill_level', 'years_of_experience', 'certification_id', 'created_by', 'updated_by', 'deleted_by'];
    protected $casts = ['years_of_experience' => 'integer'];
}
