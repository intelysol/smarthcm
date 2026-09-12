<?php

namespace App\Domains\WorkforceAdmin\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpsDataQualityResult extends Model
{
    use HasUuids;

    protected $table = 'hcm_ops_data_quality_results';

    protected $fillable = [
        'tenant_id',
        'run_id',
        'rule_id',
        'employee_id',
        'entity_type',
        'entity_id',
        'violation_message',
        'status',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function run(): BelongsTo
    {
        return $this->belongsTo(OpsDataQualityRun::class, 'run_id');
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(OpsDataQualityRule::class, 'rule_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
