<?php

namespace App\Domains\Payroll\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayrollPolicy extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'payroll_policies';

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'proration_method',
        'rounding_method',
        'overtime_rate_multiplier',
        'weekend_overtime_multiplier',
        'holiday_overtime_multiplier',
        'variance_threshold_percentage',
        'deduction_priority_order',
        'is_default',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'overtime_rate_multiplier' => 'decimal:2',
        'weekend_overtime_multiplier' => 'decimal:2',
        'holiday_overtime_multiplier' => 'decimal:2',
        'variance_threshold_percentage' => 'decimal:2',
        'deduction_priority_order' => 'array',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
