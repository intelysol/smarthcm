<?php

namespace App\Domains\Payroll\Models;

use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayrollLegalEntity extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'payroll_legal_entities';

    protected $fillable = [
        'tenant_id',
        'company_id',
        'code',
        'name',
        'currency',
        'country',
        'region',
        'timezone',
        'default_pay_frequency',
        'tax_configuration',
        'bank_configuration',
        'accounting_configuration',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tax_configuration' => 'array',
        'bank_configuration' => 'array',
        'accounting_configuration' => 'array',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function calendars(): HasMany
    {
        return $this->hasMany(PayrollCalendar::class);
    }

    public function periods(): HasMany
    {
        return $this->hasMany(PayrollPeriod::class);
    }

    public function runs(): HasMany
    {
        return $this->hasMany(PayrollRun::class);
    }
}
