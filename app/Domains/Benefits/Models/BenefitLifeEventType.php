<?php

namespace App\Domains\Benefits\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BenefitLifeEventType extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'benefit_life_event_types';

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'description',
        'notification_window_days',
        'election_window_days',
        'documentation_deadline_days',
        'requires_document',
        'effective_date_rule',
        'is_active',
    ];

    protected $casts = [
        'notification_window_days' => 'integer',
        'election_window_days' => 'integer',
        'documentation_deadline_days' => 'integer',
        'requires_document' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(BenefitLifeEvent::class, 'life_event_type_id');
    }
}
