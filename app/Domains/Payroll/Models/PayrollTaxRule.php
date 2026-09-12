<?php

namespace App\Domains\Payroll\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayrollTaxRule extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'payroll_tax_rules';

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'country',
        'region',
        'calculation_mode',
        'flat_rate_percentage',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'flat_rate_percentage' => 'decimal:4',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(PayrollTaxRuleVersion::class);
    }
}
