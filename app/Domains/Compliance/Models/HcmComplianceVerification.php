<?php

namespace App\Domains\Compliance\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class HcmComplianceVerification extends Model
{
    use HasUuids;

    protected $table = 'hcm_compliance_verifications';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'verifiable_type',
        'verifiable_id',
        'verification_source',
        'status',
        'verified_by',
        'verified_at',
        'verification_reference',
        'verification_notes',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function verifiable(): MorphTo
    {
        return $this->morphTo();
    }
}
