<?php

namespace App\Domains\SelfService\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrServiceFormDefinition extends Model
{
    use HasUuids;

    protected $table = 'hr_service_form_definitions';

    protected $fillable = [
        'tenant_id',
        'hr_service_version_id',
        'form_name',
        'schema',
    ];

    protected $casts = [
        'schema' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(HrServiceVersion::class, 'hr_service_version_id');
    }
}
