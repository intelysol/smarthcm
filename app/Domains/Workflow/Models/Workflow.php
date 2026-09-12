<?php

namespace App\Domains\Workflow\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Workflow extends WorkflowModel
{
    use SoftDeletes;

    protected $fillable = ['tenant_id', 'name', 'trigger', 'version', 'status', 'conditions', 'created_by', 'updated_by'];
    protected $casts = ['conditions' => 'array'];
    public function steps(): HasMany { return $this->hasMany(WorkflowStep::class)->orderBy('step_order'); }
}
