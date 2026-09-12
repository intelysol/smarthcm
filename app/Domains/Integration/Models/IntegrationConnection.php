<?php
namespace App\Domains\Integration\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class IntegrationConnection extends Model
{
    use HasUuids;
    protected $fillable = ['tenant_id', 'connector_id', 'name', 'configuration', 'encrypted_credentials', 'status', 'credential_expires_at'];
    protected function casts(): array { return ['configuration' => 'array', 'encrypted_credentials' => 'encrypted:array', 'credential_expires_at' => 'datetime']; }
    public function connector(): BelongsTo { return $this->belongsTo(IntegrationConnector::class, 'connector_id'); }
}
