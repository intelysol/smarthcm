<?php

namespace App\Domains\Organization\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Designation extends OrganizationModel
{
    protected $fillable = ['tenant_id', 'designation_code', 'designation_name', 'department_id', 'job_grade_id', 'description', 'created_by', 'updated_by', 'deleted_by'];

    public function getNameAttribute(): ?string
    {
        return $this->attributes['designation_name'] ?? null;
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function jobGrade(): BelongsTo
    {
        return $this->belongsTo(JobGrade::class);
    }
}
