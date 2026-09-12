<?php

namespace App\Domains\SelfService\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrAnnouncementAcknowledgement extends Model
{
    use HasUuids;

    protected $table = 'hr_announcement_acknowledgements';

    protected $fillable = [
        'tenant_id',
        'hr_announcement_id',
        'employee_id',
        'viewed_at',
        'acknowledged_at',
        'ip_address',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
        'acknowledged_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function announcement(): BelongsTo
    {
        return $this->belongsTo(HrAnnouncement::class, 'hr_announcement_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
