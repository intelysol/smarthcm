<?php

namespace App\Domains\Organization\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends OrganizationModel
{
    protected $fillable = ['tenant_id', 'company_id', 'branch_code', 'branch_name', 'region', 'address', 'contact_person', 'email', 'phone', 'latitude', 'longitude', 'working_days', 'status', 'created_by', 'updated_by', 'deleted_by'];

    protected $casts = ['working_days' => 'array', 'latitude' => 'decimal:7', 'longitude' => 'decimal:7'];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function businessUnits(): HasMany
    {
        return $this->hasMany(BusinessUnit::class);
    }
}
