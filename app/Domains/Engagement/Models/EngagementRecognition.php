<?php

namespace App\Domains\Engagement\Models;

use App\Domains\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EngagementRecognition extends EngagementModel
{
    use SoftDeletes;

    protected $table = 'engagement_recognitions';

    protected $fillable = [
        'tenant_id',
        'sender_employee_id',
        'recipient_employee_id',
        'recognition_type',
        'value_tag',
        'title',
        'message',
        'visibility',
        'status',
        'moderated_by',
        'moderated_at',
        'likes_count',
    ];

    protected function casts(): array
    {
        return [
            'moderated_at' => 'datetime',
            'likes_count' => 'integer',
        ];
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'sender_employee_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'recipient_employee_id');
    }

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'moderated_by');
    }
}
