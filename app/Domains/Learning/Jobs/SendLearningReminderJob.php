<?php

namespace App\Domains\Learning\Jobs;

use App\Domains\Communication\Services\CommunicationService;
use App\Domains\Learning\Models\LearningRequirementAssignment;
use App\Domains\Platform\Contracts\TenantContext;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendLearningReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public readonly string $tenantId) {}

    public function handle(CommunicationService $communicationService, TenantContext $tenantContext): void
    {
        $tenant = Tenant::query()->find($this->tenantId);
        if (! $tenant) return;
        $tenantContext->set($tenant);

        $upcoming = LearningRequirementAssignment::query()
            ->where('tenant_id', $this->tenantId)
            ->whereIn('status', ['assigned', 'enrolled', 'in_progress'])
            ->whereBetween('due_at', [now(), now()->addDays(7)])
            ->with(['employee.user', 'course'])
            ->get();

        foreach ($upcoming as $assignment) {
            if ($assignment->employee?->user?->email) {
                $communicationService->queue($this->tenantId, [
                    'recipient_id' => $assignment->employee->user_id,
                    'channel' => 'email',
                    'subject' => 'Training Reminder: ' . ($assignment->course?->title ?? 'Required Course'),
                    'body' => 'Please complete your training before ' . $assignment->due_at->toFormattedDateString(),
                ]);
            }
        }
    }
}
