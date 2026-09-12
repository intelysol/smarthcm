<?php

namespace App\Domains\SelfService\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrServiceAssignmentRule extends Model
{
    use HasUuids;

    protected $table = 'hr_service_assignment_rules';

    protected $fillable = [
        'tenant_id',
        'name',
        'priority',
        'hr_service_category_id',
        'hr_service_definition_id',
        'branch_id',
        'department_id',
        'target_queue_id',
        'target_user_id',
        'criteria',
        'is_active',
    ];

    protected $casts = [
        'priority' => 'integer',
        'criteria' => 'array',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function targetQueue(): BelongsTo
    {
        return $this->belongsTo(HrServiceQueue::class, 'target_queue_id');
    }

    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }
}
