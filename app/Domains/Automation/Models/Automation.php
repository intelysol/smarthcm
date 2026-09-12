<?php
namespace App\Domains\Automation\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Automation extends Model
{
    use HasUuids;
    protected $fillable = ['tenant_id', 'template_id', 'key', 'name', 'version', 'status', 'definition', 'settings', 'created_by'];
    protected function casts(): array { return ['definition' => 'array', 'settings' => 'array']; }
    public function executions(): HasMany { return $this->hasMany(AutomationExecution::class); }
    public function triggers(): HasMany { return $this->hasMany(AutomationTrigger::class); }
}
