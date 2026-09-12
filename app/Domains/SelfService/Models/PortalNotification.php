<?php

namespace App\Domains\SelfService\Models;

use App\Domains\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortalNotification extends PortalModel
{
    protected $fillable = ['tenant_id', 'employee_id', 'type', 'title', 'body', 'data', 'read_at', 'archived_at'];

    protected $casts = ['data' => 'array', 'read_at' => 'datetime', 'archived_at' => 'datetime'];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
