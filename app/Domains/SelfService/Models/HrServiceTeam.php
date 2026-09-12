<?php

namespace App\Domains\SelfService\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HrServiceTeam extends Model
{
    use HasUuids;

    protected $table = 'hr_service_teams';

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'description',
        'lead_user_id',
        'supported_categories',
        'supported_countries',
        'working_hours_calendar',
        'is_active',
    ];

    protected $casts = [
        'supported_categories' => 'array',
        'supported_countries' => 'array',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lead_user_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(HrServiceTeamMember::class, 'hr_service_team_id');
    }
}
