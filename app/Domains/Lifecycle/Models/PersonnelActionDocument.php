<?php

namespace App\Domains\Lifecycle\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonnelActionDocument extends Model
{
    use HasUuids;

    protected $table = 'personnel_action_documents';

    protected $fillable = [
        'tenant_id',
        'personnel_action_request_id',
        'document_type',
        'title',
        'file_path',
        'generated_at',
    ];

    protected $casts = [
        'generated_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(PersonnelActionRequest::class, 'personnel_action_request_id');
    }
}
