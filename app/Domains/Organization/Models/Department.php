<?php

namespace App\Domains\Organization\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends OrganizationModel
{
    protected $fillable = ['tenant_id', 'business_unit_id', 'parent_department_id', 'department_code', 'department_name', 'manager_id', 'description', 'status', 'created_by', 'updated_by', 'deleted_by'];

    public function getNameAttribute(): ?string
    {
        return $this->attributes['department_name'] ?? null;
    }

    public function businessUnit(): BelongsTo
    {
        return $this->belongsTo(BusinessUnit::class);
    }

    public function parentDepartment(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_department_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function childDepartments(): HasMany
    {
        return $this->hasMany(self::class, 'parent_department_id');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }
}
