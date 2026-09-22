<?php

namespace App\Domains\EmployeeExperience\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmEmployeeQuickAction extends Model
{
    use HasUuids;

    protected $table = 'hcm_employee_quick_actions';

    protected $fillable = [
        'tenant_id',
        'key',
        'title',
        'icon',
        'route',
        'required_permission',
        'is_active',
        'display_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
