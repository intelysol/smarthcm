<?php

namespace App\Domains\WorkforceAdmin\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpsReconciliationResult extends Model
{
    use HasUuids;

    protected $table = 'hcm_ops_reconciliation_results';

    protected $fillable = [
        'tenant_id',
        'rule_id',
        'employee_id',
        'reconciliation_status',
        'discrepancy_details',
        'checked_at',
    ];

    protected $casts = [
        'checked_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(OpsReconciliationRule::class, 'rule_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
