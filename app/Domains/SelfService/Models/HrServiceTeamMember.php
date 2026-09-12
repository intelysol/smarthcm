<?php

namespace App\Domains\SelfService\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrServiceTeamMember extends Model
{
    use HasUuids;

    protected $table = 'hr_service_team_members';

    protected $fillable = [
        'tenant_id',
        'hr_service_team_id',
        'user_id',
        'role',
        'max_concurrent_capacity',
        'is_available',
    ];

    protected $casts = [
        'max_concurrent_capacity' => 'integer',
        'is_available' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(HrServiceTeam::class, 'hr_service_team_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
