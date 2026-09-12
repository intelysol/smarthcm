<?php

namespace App\Domains\Compliance\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmEmployeeComplianceSnapshot extends Model
{
    use HasUuids;

    protected $table = 'hcm_employee_compliance_snapshots';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'overall_status',
        'compliance_score',
        'total_requirements',
        'compliant_count',
        'expiring_count',
        'expired_count',
        'pending_count',
        'exempt_count',
        'next_expiring_item',
        'calculated_at',
    ];

    protected $casts = [
        'compliance_score' => 'decimal:2',
        'total_requirements' => 'integer',
        'compliant_count' => 'integer',
        'expiring_count' => 'integer',
        'expired_count' => 'integer',
        'pending_count' => 'integer',
        'exempt_count' => 'integer',
        'next_expiring_item' => 'array',
        'calculated_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
