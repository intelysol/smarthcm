<?php

namespace App\Domains\Offboarding\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SeparationClearance extends Model
{
    use HasUuids;

    protected $table = 'separation_clearances';

    protected $fillable = [
        'tenant_id',
        'separation_request_id',
        'department',
        'status',
        'cleared_by',
        'cleared_at',
        'waived_by',
        'waiver_reason',
        'comments',
    ];

    protected $casts = [
        'cleared_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(SeparationRequest::class, 'separation_request_id');
    }

    public function clearer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cleared_by');
    }

    public function waiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'waived_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SeparationClearanceItem::class, 'separation_clearance_id');
    }
}
