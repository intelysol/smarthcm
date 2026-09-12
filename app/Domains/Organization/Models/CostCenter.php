<?php

namespace App\Domains\Organization\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CostCenter extends OrganizationModel
{
    protected $fillable = ['tenant_id', 'cost_center_code', 'name', 'department_id', 'parent_cost_center_id', 'budget', 'status', 'created_by', 'updated_by', 'deleted_by'];

    protected $casts = ['budget' => 'decimal:2'];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function parentCostCenter(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_cost_center_id');
    }

    public function childCostCenters(): HasMany
    {
        return $this->hasMany(self::class, 'parent_cost_center_id');
    }
}
