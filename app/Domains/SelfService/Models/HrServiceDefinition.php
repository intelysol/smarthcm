<?php

namespace App\Domains\SelfService\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class HrServiceDefinition extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'hr_service_definitions';

    protected $fillable = [
        'tenant_id',
        'hr_service_category_id',
        'service_code',
        'name',
        'description',
        'icon',
        'audience',
        'confidentiality_level',
        'default_queue_id',
        'workflow_definition_id',
        'sla_policy_id',
        'current_version',
        'requires_approval',
        'requires_employee_acknowledgement',
        'allow_reopen',
        'reopen_window_days',
        'status',
        'is_popular',
    ];

    protected $casts = [
        'current_version' => 'integer',
        'requires_approval' => 'boolean',
        'requires_employee_acknowledgement' => 'boolean',
        'allow_reopen' => 'boolean',
        'reopen_window_days' => 'integer',
        'is_popular' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(HrServiceCategory::class, 'hr_service_category_id');
    }

    public function defaultQueue(): BelongsTo
    {
        return $this->belongsTo(HrServiceQueue::class, 'default_queue_id');
    }

    public function slaPolicy(): BelongsTo
    {
        return $this->belongsTo(HrServiceSlaPolicy::class, 'sla_policy_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(HrServiceVersion::class, 'hr_service_definition_id');
    }

    public function requests(): HasMany
    {
        return $this->hasMany(HrServiceRequest::class, 'hr_service_definition_id');
    }

    public function templates(): HasMany
    {
        return $this->hasMany(HrServiceTemplate::class, 'hr_service_definition_id');
    }
}
