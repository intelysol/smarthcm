<?php

namespace App\Domains\SelfService\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrAnnouncementAudience extends Model
{
    use HasUuids;

    protected $table = 'hr_announcement_audiences';

    protected $fillable = [
        'tenant_id',
        'hr_announcement_id',
        'audience_type',
        'audience_id',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function announcement(): BelongsTo
    {
        return $this->belongsTo(HrAnnouncement::class, 'hr_announcement_id');
    }
}
