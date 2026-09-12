<?php
namespace App\Domains\Automation\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
class AutomationExecutionStep extends Model
{
    use HasUuids;
    protected $fillable = ['execution_id', 'node_id', 'node_type', 'status', 'input', 'output', 'error_message', 'started_at', 'completed_at'];
    protected function casts(): array { return ['input' => 'array', 'output' => 'array', 'started_at' => 'datetime', 'completed_at' => 'datetime']; }
}
