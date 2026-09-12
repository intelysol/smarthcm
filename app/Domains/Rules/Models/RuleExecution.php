<?php

namespace App\Domains\Rules\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RuleExecution extends Model
{
    use HasUuids;
    protected $fillable = ['tenant_id', 'business_rule_id', 'subject_type', 'subject_id', 'status', 'execution_mode', 'duration_ms', 'attempt', 'input', 'output', 'trace', 'error_message', 'correlation_id', 'executed_at'];
    protected function casts(): array { return ['input' => 'array', 'output' => 'array', 'trace' => 'array', 'executed_at' => 'datetime']; }
    public function rule(): BelongsTo { return $this->belongsTo(BusinessRule::class, 'business_rule_id'); }
}
