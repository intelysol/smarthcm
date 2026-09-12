<?php

namespace App\Domains\Employee\Models;

class EmployeeCustomFieldDefinition extends EmployeeModel
{
    protected $fillable = ['tenant_id', 'field_key', 'label', 'field_type', 'options', 'is_required', 'is_active', 'created_by', 'updated_by', 'deleted_by'];
    protected $casts = ['options' => 'array', 'is_required' => 'boolean', 'is_active' => 'boolean'];
}
