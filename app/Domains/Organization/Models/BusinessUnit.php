<?php

namespace App\Domains\Organization\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BusinessUnit extends OrganizationModel
{
    protected $fillable = ['tenant_id', 'company_id', 'branch_id', 'name', 'code', 'description', 'head_id', 'status', 'created_by', 'updated_by', 'deleted_by'];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function head(): BelongsTo
    {
        return $this->belongsTo(User::class, 'head_id');
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }
}
