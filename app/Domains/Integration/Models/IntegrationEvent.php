<?php
namespace App\Domains\Integration\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
class IntegrationEvent extends Model
{
    use HasUuids;
    protected $fillable = ['tenant_id', 'event_type', 'subject_type', 'subject_id', 'payload', 'occurred_at', 'published_at'];
    protected function casts(): array { return ['payload' => 'array', 'occurred_at' => 'datetime', 'published_at' => 'datetime']; }
}
