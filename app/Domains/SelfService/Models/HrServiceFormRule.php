<?php

namespace App\Domains\SelfService\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrServiceFormRule extends Model
{
    use HasUuids;

    protected $table = 'hr_service_form_rules';

    protected $fillable = [
        'tenant_id',
        'hr_service_definition_id',
        'source_field_key',
        'operator',
        'trigger_values',
        'action',
        'target_field_key',
        'action_payload',
        'is_active',
    ];

    protected $casts = [
        'trigger_values' => 'array',
        'action_payload' => 'array',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function serviceDefinition(): BelongsTo
    {
        return $this->belongsTo(HrServiceDefinition::class, 'hr_service_definition_id');
    }
}
