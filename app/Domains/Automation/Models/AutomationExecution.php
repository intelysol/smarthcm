<?php
namespace App\Domains\Automation\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class AutomationExecution extends Model
{
    use HasUuids;
    protected $fillable = ['tenant_id', 'automation_id', 'trigger_type', 'status', 'current_node_id', 'context', 'duration_ms', 'started_at', 'completed_at'];
    protected function casts(): array { return ['context' => 'array', 'started_at' => 'datetime', 'completed_at' => 'datetime']; }
    public function steps(): HasMany { return $this->hasMany(AutomationExecutionStep::class, 'execution_id'); }
}
