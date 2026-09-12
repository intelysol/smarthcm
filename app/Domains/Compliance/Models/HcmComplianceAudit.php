<?php

namespace App\Domains\Compliance\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmComplianceAudit extends Model
{
    use HasUuids;

    protected $table = 'hcm_compliance_audits';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'action',
        'entity_type',
        'entity_id',
        'actor_id',
        'details',
    ];

    protected $casts = [
        'details' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
