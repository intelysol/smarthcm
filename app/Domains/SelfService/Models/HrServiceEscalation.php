<?php

namespace App\Domains\SelfService\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrServiceEscalation extends Model
{
    use HasUuids;

    protected $table = 'hr_service_escalations';

    protected $fillable = [
        'tenant_id',
        'hr_service_request_id',
        'escalation_level',
        'reason',
        'escalated_to_user_id',
        'escalated_at',
        'is_resolved',
    ];

    protected $casts = [
        'escalation_level' => 'integer',
        'escalated_at' => 'datetime',
        'is_resolved' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(HrServiceRequest::class, 'hr_service_request_id');
    }

    public function escalatedToUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'escalated_to_user_id');
    }
}
