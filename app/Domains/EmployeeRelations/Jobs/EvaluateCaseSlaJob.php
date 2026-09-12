<?php

namespace App\Domains\EmployeeRelations\Jobs;

use App\Domains\EmployeeRelations\Events\EmployeeRelationCaseEscalated;
use App\Domains\EmployeeRelations\Models\EmployeeRelationCaseSla;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EvaluateCaseSlaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public ?string $tenantId = null
    ) {}

    public function handle(): void
    {
        $query = EmployeeRelationCaseSla::query()->where('status', 'pending');
        if ($this->tenantId) {
            $query->where('tenant_id', $this->tenantId);
        }

        $pendingSlas = $query->with('case')->get();

        foreach ($pendingSlas as $sla) {
            $case = $sla->case;
            if (! $case || in_array($case->status, ['closed', 'archived', 'cancelled', 'rejected', 'withdrawn'], true)) {
                $sla->update(['status' => 'exempt']);
                continue;
            }

            if ($sla->due_at->isPast()) {
                $sla->update([
                    'status' => 'breached',
                    'breached_at' => now(),
                    'escalation_sent_at' => now(),
                ]);

                event(new EmployeeRelationCaseEscalated($case, "SLA Breached: {$sla->sla_type} exceeded target of {$sla->target_hours} hours."));
            } elseif ($sla->due_at->diffInHours(now()) <= 12 && ! $sla->reminder_sent_at) {
                $sla->update(['reminder_sent_at' => now()]);
            }
        }
    }
}
