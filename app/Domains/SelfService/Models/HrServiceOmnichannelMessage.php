<?php

namespace App\Domains\SelfService\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrServiceOmnichannelMessage extends Model
{
    use HasUuids;

    protected $table = 'hr_service_omnichannel_messages';

    protected $fillable = [
        'tenant_id',
        'channel',
        'external_message_id',
        'sender_identifier',
        'sender_name',
        'matched_employee_id',
        'hr_service_request_id',
        'subject',
        'body',
        'raw_payload',
        'processing_status',
    ];

    protected $casts = [
        'raw_payload' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'matched_employee_id');
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(HrServiceRequest::class, 'hr_service_request_id');
    }
}
