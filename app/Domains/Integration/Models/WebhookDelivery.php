<?php

namespace App\Domains\Integration\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookDelivery extends Model
{
    use HasUuids;

    protected $table = 'webhook_deliveries';

    protected $fillable = [
        'subscription_id',
        'event_id',
        'status',
        'attempt',
        'response_code',
        'response_body',
        'delivered_at',
        'next_retry_at',
    ];

    protected function casts(): array
    {
        return [
            'attempt' => 'integer',
            'response_code' => 'integer',
            'delivered_at' => 'datetime',
            'next_retry_at' => 'datetime',
        ];
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(WebhookSubscription::class, 'subscription_id');
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(IntegrationEvent::class, 'event_id');
    }
}
