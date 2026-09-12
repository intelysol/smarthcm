<?php

namespace App\Domains\WorkforceCost\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HcmWorkforceCostSnapshot extends Model
{
    use HasUuids;

    protected $table = 'hcm_workforce_cost_snapshots';

    protected $fillable = [
        'tenant_id',
        'model_id',
        'snapshot_number',
        'period_name',
        'period_start',
        'period_end',
        'version',
        'status',
        'currency',
        'total_workforce_cost',
        'total_direct_labor',
        'total_indirect_labor',
        'total_burden',
        'total_overtime_cost',
        'total_benefits_cost',
        'total_contractor_cost',
        'total_absence_cost',
        'total_vacancy_cost',
        'total_fte',
        'total_headcount',
        'total_labor_hours',
        'idempotency_key',
        'locked_at',
        'created_by',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'version' => 'integer',
        'total_workforce_cost' => 'decimal:4',
        'total_direct_labor' => 'decimal:4',
        'total_indirect_labor' => 'decimal:4',
        'total_burden' => 'decimal:4',
        'total_overtime_cost' => 'decimal:4',
        'total_benefits_cost' => 'decimal:4',
        'total_contractor_cost' => 'decimal:4',
        'total_absence_cost' => 'decimal:4',
        'total_vacancy_cost' => 'decimal:4',
        'total_fte' => 'decimal:2',
        'total_headcount' => 'integer',
        'total_labor_hours' => 'decimal:2',
        'locked_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function model(): BelongsTo
    {
        return $this->belongsTo(HcmWorkforceCostModel::class, 'model_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(HcmWorkforceCostLine::class, 'snapshot_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}