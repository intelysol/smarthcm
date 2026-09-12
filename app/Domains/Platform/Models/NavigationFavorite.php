<?php

namespace App\Domains\Platform\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NavigationFavorite extends Model
{
    use HasUuids;

    protected $fillable = ['tenant_id', 'user_id', 'route_name', 'label', 'url', 'position'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
