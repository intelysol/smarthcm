<?php

namespace App\Domains\Organization\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkLocation extends OrganizationModel
{
    protected $fillable = ['tenant_id', 'branch_id', 'name', 'address', 'latitude', 'longitude', 'shift_id', 'capacity', 'created_by', 'updated_by', 'deleted_by'];

    protected $casts = ['latitude' => 'decimal:7', 'longitude' => 'decimal:7', 'capacity' => 'integer'];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }
}
