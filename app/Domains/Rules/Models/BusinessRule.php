<?php

namespace App\Domains\Rules\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusinessRule extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = ['tenant_id', 'rule_group_id', 'key', 'name', 'description', 'domain', 'category', 'trigger', 'priority', 'version', 'status', 'effective_from', 'effective_to', 'execution_mode', 'conditions', 'actions', 'settings', 'tags', 'created_by', 'updated_by', 'reviewed_by', 'reviewed_at'];
    protected function casts(): array { return ['conditions' => 'array', 'actions' => 'array', 'settings' => 'array', 'tags' => 'array', 'effective_from' => 'datetime', 'effective_to' => 'datetime', 'reviewed_at' => 'datetime']; }
    public function executions(): HasMany { return $this->hasMany(RuleExecution::class, 'business_rule_id'); }
    public function dependencies(): HasMany { return $this->hasMany(RuleDependency::class, 'business_rule_id'); }
}
