<?php

namespace App\Domains\EmployeeRelations\Models;

use App\Domains\Documents\Models\Document;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeRelationCorrespondence extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'employee_relation_correspondence';

    protected $fillable = [
        'tenant_id',
        'case_id',
        'channel',
        'sender_id',
        'recipient_email',
        'recipient_name',
        'recipient_user_id',
        'subject',
        'body',
        'sent_at',
        'delivery_status',
        'template_id',
        'document_id',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function case(): BelongsTo
    {
        return $this->belongsTo(EmployeeRelationCase::class, 'case_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipientUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(EmployeeRelationCorrespondenceTemplate::class, 'template_id');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'document_id');
    }
}
