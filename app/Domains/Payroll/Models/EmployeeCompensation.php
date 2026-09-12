<?php

namespace App\Domains\Payroll\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeCompensation extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'employee_compensations';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'compensation_structure_id',
        'currency',
        'pay_frequency',
        'base_salary',
        'gross_salary',
        'effective_from',
        'effective_to',
        'reason_for_change',
        'status',
        'is_active',
        'approved_by',
        'approved_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'base_salary' => 'decimal:4',
        'gross_salary' => 'decimal:4',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'is_active' => 'boolean',
        'approved_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function structure(): BelongsTo
    {
        return $this->belongsTo(CompensationStructure::class, 'compensation_structure_id');
    }

    public function components(): HasMany
    {
        return $this->hasMany(EmployeeCompensationComponent::class);
    }
}
