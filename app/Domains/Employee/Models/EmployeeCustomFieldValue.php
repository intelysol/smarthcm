<?php

namespace App\Domains\Employee\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeCustomFieldValue extends EmployeeChildModel
{
    protected $fillable = ['tenant_id', 'employee_id', 'field_definition_id', 'value', 'created_by', 'updated_by', 'deleted_by'];
    protected $casts = ['value' => 'array'];

    public function definition(): BelongsTo
    {
        return $this->belongsTo(EmployeeCustomFieldDefinition::class, 'field_definition_id');
    }
}
