<?php

namespace App\Domains\Rules\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RuleDependency extends Model
{
    use HasUuids;
    protected $fillable = ['business_rule_id', 'dependency_type', 'dependency_key', 'metadata'];
    protected function casts(): array { return ['metadata' => 'array']; }
    public function rule(): BelongsTo { return $this->belongsTo(BusinessRule::class, 'business_rule_id'); }
}
