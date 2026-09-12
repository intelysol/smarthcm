<?php
namespace App\Domains\Integration\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class IntegrationConnector extends Model
{
    use HasUuids;
    protected $fillable = ['tenant_id', 'key', 'name', 'connector_type', 'manifest', 'status'];
    protected function casts(): array { return ['manifest' => 'array']; }
    public function connections(): HasMany { return $this->hasMany(IntegrationConnection::class, 'connector_id'); }
}
