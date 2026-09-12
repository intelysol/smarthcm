<?php

namespace App\Domains\SelfService\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrKnowledgeFeedback extends Model
{
    use HasUuids;

    protected $table = 'hr_knowledge_feedback';

    protected $fillable = [
        'tenant_id',
        'hr_knowledge_article_id',
        'employee_id',
        'is_helpful',
        'comments',
    ];

    protected $casts = [
        'is_helpful' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(HrKnowledgeArticle::class, 'hr_knowledge_article_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
