<?php

namespace App\Domains\SelfService\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrServiceRequestDocument extends Model
{
    use HasUuids;

    protected $table = 'hr_service_request_documents';

    protected $fillable = [
        'tenant_id',
        'hr_service_request_id',
        'uploaded_by_user_id',
        'document_title',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'is_confidential',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'is_confidential' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(HrServiceRequest::class, 'hr_service_request_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }
}
