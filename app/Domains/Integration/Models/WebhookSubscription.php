<?php
namespace App\Domains\Integration\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
class WebhookSubscription extends Model
{
    use HasUuids;
    protected $fillable = ['tenant_id', 'connection_id', 'url', 'event_filters', 'secret_reference', 'status', 'max_retries'];
    protected function casts(): array { return ['event_filters' => 'array']; }
}
