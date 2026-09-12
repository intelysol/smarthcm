<?php

namespace App\Domains\Performance\Services;

use App\Domains\Events\Services\EventBus;
use App\Domains\Performance\Models\PerformanceGoal;
use App\Domains\Performance\Models\PerformanceGoalProgress;
use App\Domains\Shared\Services\AuditService;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class PerformanceGoalService
{
    public function __construct(private readonly AuditService $audit, private readonly EventBus $events) {}

    /** @param array<string,mixed> $attributes */
    public function create(User $actor, array $attributes): PerformanceGoal
    {
        return DB::transaction(function () use ($actor, $attributes): PerformanceGoal {
            $this->assertEditableCycle((string) $attributes['cycle_id']);
            $goal = PerformanceGoal::query()->create([...$attributes, 'created_by' => $actor->id, 'updated_by' => $actor->id]);
            $this->assertWeightTotal($goal);
            $this->record($actor, 'PerformanceGoalCreated', 'created', $goal);
            return $goal;
        });
    }

    /** @param array<string,mixed> $attributes */
    public function update(User $actor, PerformanceGoal $goal, array $attributes, int $version): PerformanceGoal
    {
        return DB::transaction(function () use ($actor, $goal, $attributes, $version): PerformanceGoal {
            $goal = PerformanceGoal::query()->lockForUpdate()->findOrFail($goal->id);
            if ($goal->version !== $version) { throw ValidationException::withMessages(['version' => 'This goal was changed by another user. Refresh and try again.']); }
            $this->assertEditableCycle((string) $goal->cycle_id);
            $before = $goal->getOriginal(); $goal->fill($attributes); $goal->version++; $goal->updated_by = $actor->id; $goal->save();
            $this->assertWeightTotal($goal); $this->record($actor, 'PerformanceGoalChanged', 'updated', $goal, $before);
            return $goal->refresh();
        });
    }

    public function recordProgress(User $actor, PerformanceGoal $goal, ?float $newValue, float $progress, ?string $comment, string $source): PerformanceGoalProgress
    {
        if ($progress < 0 || $progress > 100) { throw ValidationException::withMessages(['progress_percentage' => 'Progress must be between 0 and 100.']); }
        return DB::transaction(function () use ($actor, $goal, $newValue, $progress, $comment, $source): PerformanceGoalProgress {
            $goal = PerformanceGoal::query()->lockForUpdate()->findOrFail($goal->id);
            $record = PerformanceGoalProgress::query()->create(['goal_id' => $goal->id, 'recorded_by' => $actor->id, 'recorded_at' => now(), 'previous_value' => $goal->current_value, 'new_value' => $newValue, 'progress_percentage' => $progress, 'comment' => $comment, 'source' => $source]);
            $goal->update(['current_value' => $newValue, 'progress_percentage' => $progress, 'version' => $goal->version + 1, 'updated_by' => $actor->id]);
            $this->record($actor, 'PerformanceGoalChanged', 'progress_recorded', $goal);
            return $record;
        });
    }

    private function assertEditableCycle(string $cycleId): void { $status = \App\Domains\Performance\Models\PerformanceCycle::query()->findOrFail($cycleId)->status; if (in_array($status, ['self_assessment', 'manager_assessment', 'calibration', 'finalization', 'closed', 'cancelled', 'archived'], true)) throw ValidationException::withMessages(['cycle' => 'Goals are frozen for this cycle stage.']); }
    private function assertWeightTotal(PerformanceGoal $goal): void { if ($goal->employee_id === null) return; $total = (float) PerformanceGoal::query()->where('cycle_id', $goal->cycle_id)->where('employee_id', $goal->employee_id)->whereNull('deleted_at')->sum('weight'); if ($total > 100.01) throw ValidationException::withMessages(['weight' => 'Employee goal weights cannot exceed 100%.']); }
    /** @param array<string,mixed>|null $before */
    private function record(User $actor, string $event, string $action, PerformanceGoal $goal, ?array $before = null): void { $this->audit->record((string) $actor->tenant_id, $event, $action, PerformanceGoal::class, (string) $goal->id, $actor->id, $before, ['title' => $goal->title, 'status' => $goal->status]); $this->events->publish(['tenant_id' => $actor->tenant_id, 'event_type' => $event, 'event_version' => 1, 'aggregate_type' => 'performance_goal', 'aggregate_id' => $goal->id, 'actor_id' => (string) $actor->id, 'source_module' => 'performance', 'payload' => ['goal_id' => $goal->id]]); }
}
