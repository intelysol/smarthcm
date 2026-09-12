<?php

namespace App\Domains\Payroll\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayrollArrear extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'payroll_arrears';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'title',
        'origin_start_date',
        'origin_end_date',
        'previous_amount',
        'revised_amount',
        'difference_amount',
        'currency',
        'payroll_period_id',
        'status',
        'reason',
        'approved_by',
        'approved_at',
        'created_by',
    ];

    protected $casts = [
        'origin_start_date' => 'date',
        'origin_end_date' => 'date',
        'previous_amount' => 'decimal:4',
        'revised_amount' => 'decimal:4',
        'difference_amount' => 'decimal:4',
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

    public function period(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class, 'payroll_period_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
