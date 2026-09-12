<?php

namespace App\Domains\Performance\Services;

use App\Domains\Events\Services\EventBus;
use App\Domains\Performance\Models\PerformanceCycle;
use App\Domains\Performance\Models\PerformanceCycleConfiguration;
use App\Domains\Shared\Services\AuditService;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class PerformanceCycleService
{
    private const TRANSITIONS = ['draft' => ['configured', 'cancelled', 'archived'], 'configured' => ['open', 'draft', 'cancelled'], 'open' => ['goal_setting', 'cancelled'], 'goal_setting' => ['in_progress', 'cancelled'], 'in_progress' => ['self_assessment', 'cancelled'], 'self_assessment' => ['manager_assessment', 'cancelled'], 'manager_assessment' => ['calibration', 'finalization', 'cancelled'], 'calibration' => ['finalization', 'cancelled'], 'finalization' => ['closed', 'cancelled'], 'closed' => ['archived'], 'cancelled' => ['archived'], 'archived' => []];
    public function __construct(private readonly AuditService $audit, private readonly EventBus $events) {}
    /** @param array<string,mixed> $attributes */
    public function create(User $actor, array $attributes): PerformanceCycle { return DB::transaction(function () use ($actor, $attributes): PerformanceCycle { $cycle = PerformanceCycle::query()->create([...$attributes, 'created_by' => $actor->id, 'updated_by' => $actor->id]); PerformanceCycleConfiguration::query()->create(['cycle_id' => $cycle->id]); $this->record($actor, 'PerformanceCycleCreated', 'created', $cycle); return $cycle->load('configuration'); }); }
    /** @param array<string,mixed> $attributes */
    public function update(User $actor, PerformanceCycle $cycle, array $attributes): PerformanceCycle { if (! in_array($cycle->status, ['draft', 'configured'], true)) throw ValidationException::withMessages(['cycle' => 'Only draft or configured cycles may be changed.']); $cycle->fill($attributes); $cycle->version++; $cycle->updated_by = $actor->id; $cycle->save(); $this->record($actor, 'PerformanceCycleCreated', 'updated', $cycle); return $cycle->fresh('configuration'); }
    /** @param array<string,mixed> $attributes */
    public function configure(User $actor, PerformanceCycle $cycle, array $attributes): PerformanceCycle { if (! in_array($cycle->status, ['draft', 'configured'], true)) throw ValidationException::withMessages(['cycle' => 'Only draft or configured cycles may be changed.']); $config = $cycle->configuration()->firstOrFail(); $config->fill($attributes); $weight = (float) $config->goal_weight + (float) $config->competency_weight + (float) $config->feedback_weight; if (abs($weight - 100) > .01) throw ValidationException::withMessages(['weights' => 'Configured weights must total 100%.']); $config->save(); $cycle->update(['status' => 'configured', 'updated_by' => $actor->id]); $this->record($actor, 'PerformanceCycleCreated', 'configured', $cycle); return $cycle->fresh('configuration'); }
    public function transition(User $actor, PerformanceCycle $cycle, string $target): PerformanceCycle { $cycle = PerformanceCycle::query()->lockForUpdate()->findOrFail($cycle->id); if (! in_array($target, self::TRANSITIONS[$cycle->status] ?? [], true)) throw ValidationException::withMessages(['status' => "Cannot transition from {$cycle->status} to {$target}."]); if ($target === 'open' && $cycle->configuration === null) throw ValidationException::withMessages(['configuration' => 'A configured cycle is required before opening.']); $previous = $cycle->status; $cycle->update(['status' => $target, 'updated_by' => $actor->id, 'version' => $cycle->version + 1]); $this->record($actor, $target === 'closed' ? 'PerformanceCycleClosed' : 'PerformanceCycleOpened', "{$previous}_to_{$target}", $cycle); return $cycle->refresh(); }
    private function record(User $actor, string $event, string $action, PerformanceCycle $cycle): void { $this->audit->record((string) $actor->tenant_id, $event, $action, PerformanceCycle::class, (string) $cycle->id, $actor->id, null, ['name' => $cycle->name, 'status' => $cycle->status]); $this->events->publish(['tenant_id' => $actor->tenant_id, 'event_type' => $event, 'event_version' => 1, 'aggregate_type' => 'performance_cycle', 'aggregate_id' => $cycle->id, 'actor_id' => (string) $actor->id, 'source_module' => 'performance', 'payload' => ['cycle_id' => $cycle->id]]); }
}
