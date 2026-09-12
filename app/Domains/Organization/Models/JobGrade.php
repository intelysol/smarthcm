<?php

namespace App\Domains\Organization\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class JobGrade extends OrganizationModel
{
    protected $fillable = ['tenant_id', 'grade_code', 'grade_name', 'level', 'minimum_salary', 'maximum_salary', 'description', 'created_by', 'updated_by', 'deleted_by'];

    protected $casts = ['level' => 'integer', 'minimum_salary' => 'decimal:2', 'maximum_salary' => 'decimal:2'];

    public function designations(): HasMany
    {
        return $this->hasMany(Designation::class);
    }
}
