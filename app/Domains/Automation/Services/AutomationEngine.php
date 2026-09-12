<?php
namespace App\Domains\Automation\Services;
use App\Domains\Automation\Models\{Automation, AutomationExecution};
use Illuminate\Support\Facades\DB;
class AutomationEngine
{
    public function run(Automation $automation, string $tenantId, string $trigger, array $context = [], bool $dryRun = false): AutomationExecution
    {
        return DB::transaction(function () use ($automation, $tenantId, $trigger, $context, $dryRun): AutomationExecution {
            $execution = AutomationExecution::query()->create(['tenant_id' => $tenantId, 'automation_id' => $automation->id, 'trigger_type' => $trigger, 'status' => 'running', 'context' => $context, 'started_at' => now()]);
            $values = $context; $started = hrtime(true);
            try {
                foreach (($automation->definition['nodes'] ?? []) as $node) {
                    $step = $execution->steps()->create(['node_id' => $node['id'] ?? uniqid('node_', true), 'node_type' => $node['type'] ?? 'action', 'status' => 'running', 'input' => $values, 'started_at' => now()]);
                    $type = strtolower((string) ($node['type'] ?? 'action'));
                    if ($type === 'condition' && (($node['when'] ?? true) === false)) { $step->update(['status' => 'skipped', 'completed_at' => now()]); continue; }
                    $output = ['intent' => $node['action'] ?? $type, 'parameters' => $node['parameters'] ?? []];
                    $values[$node['output'] ?? ($node['id'] ?? 'last')] = $output;
                    $step->update(['status' => 'succeeded', 'output' => $output, 'completed_at' => now()]);
                    if ($type === 'wait' || $type === 'delay') { $execution->update(['status' => 'waiting', 'current_node_id' => $node['id'] ?? null]); break; }
                }
                if ($execution->status === 'running') $execution->update(['status' => $dryRun ? 'simulated' : 'completed', 'completed_at' => now(), 'duration_ms' => (int) ((hrtime(true) - $started) / 1_000_000)]);
            } catch (\Throwable $exception) { $execution->update(['status' => 'failed', 'completed_at' => now(), 'duration_ms' => (int) ((hrtime(true) - $started) / 1_000_000)]); throw $exception; }
            return $execution->load('steps');
        });
    }
}
