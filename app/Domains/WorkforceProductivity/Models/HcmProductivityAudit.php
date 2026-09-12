<?php

namespace App\Domains\WorkforceProductivity\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmProductivityAudit extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $table = 'hcm_productivity_audits';

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

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
