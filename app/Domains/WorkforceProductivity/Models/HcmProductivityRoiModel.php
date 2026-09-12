<?php

namespace App\Domains\WorkforceProductivity\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HcmProductivityRoiModel extends Model
{
    use HasUuids;

    protected $table = 'hcm_productivity_roi_models';

    protected $fillable = [
        'tenant_id',
        'model_name',
        'investment_type',
        'currency',
        'evaluation_methodology',
        'is_active',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function calculations(): HasMany
    {
        return $this->hasMany(HcmProductivityRoiCalculation::class, 'roi_model_id');
    }
}
