<?php

namespace App\Domains\Organization\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Section extends OrganizationModel
{
    protected $fillable = ['tenant_id', 'department_id', 'section_code', 'section_name', 'supervisor_id', 'created_by', 'updated_by', 'deleted_by'];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }
}
