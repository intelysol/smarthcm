<?php

namespace App\Domains\Analytics\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmAnalyticsAlertSubscription extends Model
{
    use HasUuids;

    protected $table = 'hcm_analytics_alert_subscriptions';

    protected $fillable = [
        'tenant_id',
        'hcm_analytics_alert_id',
        'user_id',
        'delivery_channel',
        'last_triggered_at',
    ];

    protected $casts = [
        'last_triggered_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function alert(): BelongsTo
    {
        return $this->belongsTo(HcmAnalyticsAlert::class, 'hcm_analytics_alert_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
