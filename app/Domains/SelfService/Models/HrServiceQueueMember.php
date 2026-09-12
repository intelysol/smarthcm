<?php

namespace App\Domains\SelfService\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrServiceQueueMember extends Model
{
    use HasUuids;

    protected $table = 'hr_service_queue_members';

    protected $fillable = [
        'tenant_id',
        'hr_service_queue_id',
        'user_id',
        'role_in_queue',
        'active_tickets_count',
        'is_available',
    ];

    protected $casts = [
        'active_tickets_count' => 'integer',
        'is_available' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function queue(): BelongsTo
    {
        return $this->belongsTo(HrServiceQueue::class, 'hr_service_queue_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
