<?php

namespace App\Domains\Benefits\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BenefitStatement extends Model
{
    use HasUuids;

    protected $table = 'benefit_statements';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'statement_year',
        'statement_date',
        'total_employer_cost',
        'total_employee_cost',
        'total_benefit_value',
        'currency',
        'statement_data',
        'status',
        'published_at',
    ];

    protected $casts = [
        'statement_year' => 'integer',
        'statement_date' => 'date',
        'total_employer_cost' => 'decimal:4',
        'total_employee_cost' => 'decimal:4',
        'total_benefit_value' => 'decimal:4',
        'statement_data' => 'array',
        'published_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
