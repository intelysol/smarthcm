<?php

namespace App\Domains\Analytics\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmAnalyticsSavedView extends Model
{
    use HasUuids;

    protected $table = 'hcm_analytics_saved_views';

    protected $fillable = [
        'tenant_id',
        'name',
        'view_scope',
        'page_context',
        'filters',
        'dimensions',
        'user_id',
    ];

    protected $casts = [
        'filters' => 'array',
        'dimensions' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
