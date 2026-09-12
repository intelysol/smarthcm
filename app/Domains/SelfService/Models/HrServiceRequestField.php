<?php

namespace App\Domains\SelfService\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrServiceRequestField extends Model
{
    use HasUuids;

    protected $table = 'hr_service_request_fields';

    protected $fillable = [
        'tenant_id',
        'hr_service_request_id',
        'field_key',
        'field_label',
        'field_type',
        'field_value',
        'raw_value',
    ];

    protected $casts = [
        'raw_value' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(HrServiceRequest::class, 'hr_service_request_id');
    }
}
