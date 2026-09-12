<?php

namespace App\Domains\Employee\Models;

class EmployeeCertification extends EmployeeChildModel
{
    protected $fillable = ['tenant_id', 'employee_id', 'certification_name', 'issuing_organization', 'issue_date', 'expiry_date', 'credential_number', 'attachment_path', 'created_by', 'updated_by', 'deleted_by'];
    protected $casts = ['issue_date' => 'date', 'expiry_date' => 'date'];
}
