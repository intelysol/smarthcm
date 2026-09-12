<?php

namespace App\Domains\WorkforceOptimization\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class HcmWorkforceOptimizationAudit extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'hcm_workforce_optimization_audits';
    protected $keyType = 'string';
    public $incrementing = false;

    public const UPDATED_AT = null; // Audit trails are append-only

    protected $fillable = [
        'tenant_id',
        'action',
        'target_type',
        'target_id',
        'user_id',
        'changes',
        'ip_address',
        'created_at',
    ];

    protected $casts = [
        'changes' => 'array',
        'created_at' => 'datetime',
    ];

    public function run(): BelongsTo
    {
        return $this->belongsTo(HcmWorkforceOptimizationRun::class, 'run_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
