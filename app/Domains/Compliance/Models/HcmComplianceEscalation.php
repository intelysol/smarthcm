<?php

namespace App\Domains\Compliance\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmComplianceEscalation extends Model
{
    use HasUuids;

    protected $table = 'hcm_compliance_escalations';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'target_type',
        'target_id',
        'tier',
        'recipient_role',
        'triggered_at',
        'is_acknowledged',
    ];

    protected $casts = [
        'triggered_at' => 'datetime',
        'is_acknowledged' => 'boolean',
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
