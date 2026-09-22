<?php
namespace App\Domains\Api\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids; use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiKey extends Model
{
    use HasUuids;

    protected $table = 'api_keys';
    protected $fillable = ['client_id', 'key_hash', 'label', 'expires_at', 'last_used_at', 'status'];
    protected $hidden = ['key_hash'];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'last_used_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(ApiClient::class, 'client_id');
    }
}
