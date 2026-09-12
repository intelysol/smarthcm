<?php

namespace App\Domains\Lifecycle\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonnelActionChange extends Model
{
    use HasUuids;

    protected $table = 'personnel_action_changes';

    protected $fillable = [
        'tenant_id',
        'personnel_action_request_id',
        'field_name',
        'entity_type',
        'entity_id',
        'old_value',
        'new_value',
        'old_value_label',
        'new_value_label',
        'change_type',
        'effective_date',
    ];

    protected $casts = [
        'effective_date' => 'date',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(PersonnelActionRequest::class, 'personnel_action_request_id');
    }
}
