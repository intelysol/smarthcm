<?php

namespace App\Domains\SelfService\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class HrServiceQueue extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'hr_service_queues';

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'description',
        'email',
        'assignment_method',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(HrServiceQueueMember::class, 'hr_service_queue_id');
    }

    public function requests(): HasMany
    {
        return $this->hasMany(HrServiceRequest::class, 'assigned_queue_id');
    }
}
