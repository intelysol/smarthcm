<?php

namespace App\Domains\Shared\Services;

use App\Domains\Shared\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

class ActivityLogService
{
    /**
     * @param array<string, mixed>|null $oldValues
     * @param array<string, mixed>|null $newValues
     */
    public function record(
        string $action,
        Model $model,
        int $actorId,
        ?array $oldValues,
        ?array $newValues,
    ): ActivityLog {
        return ActivityLog::query()->create([
            'tenant_id' => (string) $model->getAttribute('tenant_id'),
            'user_id' => $actorId,
            'action' => $action,
            'table_name' => $model->getTable(),
            'record_id' => (string) $model->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'occurred_at' => now(),
        ]);
    }
}
