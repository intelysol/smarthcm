<?php

namespace App\Domains\Payroll\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollTaxRuleVersion extends Model
{
    use HasUuids;

    protected $table = 'payroll_tax_rule_versions';

    protected $fillable = [
        'tenant_id',
        'payroll_tax_rule_id',
        'version_name',
        'effective_from',
        'effective_to',
        'standard_exemption',
        'tax_brackets',
        'is_active',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_to' => 'date',
        'standard_exemption' => 'decimal:4',
        'tax_brackets' => 'array',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function taxRule(): BelongsTo
    {
        return $this->belongsTo(PayrollTaxRule::class, 'payroll_tax_rule_id');
    }
}
