<?php

namespace App\Domains\Platform\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlatformNotification extends Model
{
    use HasUuids;

    protected $fillable = ['tenant_id', 'user_id', 'type', 'title', 'body', 'data', 'read_at', 'archived_at'];

    protected function casts(): array
    {
        return ['data' => 'array', 'read_at' => 'datetime', 'archived_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
