<?php

namespace App\Domains\Rules\Services;

use App\Domains\Rules\Models\BusinessRule;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RuleService
{
    /** @param array<string, mixed> $data */
    public function create(string $tenantId, User $actor, array $data): BusinessRule { return BusinessRule::query()->create([...$data, 'tenant_id' => $tenantId, 'version' => 1, 'status' => 'draft', 'created_by' => $actor->id, 'updated_by' => $actor->id]); }
    public function transition(BusinessRule $rule, User $actor, string $status): BusinessRule
    {
        $allowed = ['draft' => ['under_review', 'archived'], 'under_review' => ['approved', 'draft'], 'approved' => ['published', 'draft'], 'published' => ['deprecated', 'archived'], 'deprecated' => ['archived']];
        abort_unless(in_array($status, $allowed[$rule->status] ?? [], true), 422, 'Invalid rule lifecycle transition.');
        $rule->forceFill(['status' => $status, 'reviewed_by' => in_array($status, ['approved', 'published'], true) ? $actor->id : $rule->reviewed_by, 'reviewed_at' => in_array($status, ['approved', 'published'], true) ? now() : $rule->reviewed_at, 'updated_by' => $actor->id])->save();
        return $rule;
    }

    /** Create an immutable next version; published definitions are never edited in place. */
    public function version(BusinessRule $rule, User $actor): BusinessRule
    {
        return DB::transaction(function () use ($rule, $actor): BusinessRule {
            $latest = BusinessRule::query()->where('tenant_id', $rule->tenant_id)->where('key', $rule->key)->max('version');
            $copy = $rule->replicate();
            $copy->version = ((int) $latest) + 1;
            $copy->status = 'draft';
            $copy->created_by = $actor->id;
            $copy->updated_by = $actor->id;
            $copy->save();
            return $copy;
        });
    }

    public function rollback(BusinessRule $rule, BusinessRule $target, User $actor): BusinessRule
    {
        abort_unless($rule->tenant_id === $target->tenant_id && $rule->key === $target->key, 422, 'Version does not belong to this rule.');
        return $this->version($target, $actor);
    }
}
