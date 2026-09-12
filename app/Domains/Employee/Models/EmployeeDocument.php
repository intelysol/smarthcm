<?php

namespace App\Domains\Employee\Models;

class EmployeeDocument extends EmployeeChildModel
{
    protected $fillable = ['tenant_id', 'employee_id', 'document_type', 'title', 'file_path', 'mime_type', 'file_size', 'version', 'expires_at', 'metadata', 'created_by', 'updated_by', 'deleted_by'];
    protected $casts = ['file_size' => 'integer', 'version' => 'integer', 'expires_at' => 'date', 'metadata' => 'array'];
}
