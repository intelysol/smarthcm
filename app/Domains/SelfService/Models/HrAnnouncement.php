<?php

namespace App\Domains\SelfService\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class HrAnnouncement extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'hr_announcements';

    protected $fillable = [
        'tenant_id',
        'title',
        'category',
        'content',
        'priority',
        'requires_acknowledgement',
        'published_at',
        'expires_at',
        'status',
        'author_user_id',
    ];

    protected $casts = [
        'requires_acknowledgement' => 'boolean',
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }

    public function audiences(): HasMany
    {
        return $this->hasMany(HrAnnouncementAudience::class, 'hr_announcement_id');
    }

    public function acknowledgements(): HasMany
    {
        return $this->hasMany(HrAnnouncementAcknowledgement::class, 'hr_announcement_id');
    }
}
