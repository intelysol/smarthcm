<?php

namespace App\Domains\SelfService\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Branch;
use App\Domains\Organization\Models\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Announcement extends PortalModel
{
    use SoftDeletes;

    protected $fillable = ['tenant_id', 'branch_id', 'department_id', 'title', 'body', 'attachments', 'published_at', 'expires_at', 'requires_acknowledgement', 'created_by', 'updated_by'];

    protected $casts = ['attachments' => 'array', 'published_at' => 'datetime', 'expires_at' => 'datetime', 'requires_acknowledgement' => 'boolean'];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function readers(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'announcement_reads')->withPivot('acknowledged_at')->withTimestamps();
    }
}
