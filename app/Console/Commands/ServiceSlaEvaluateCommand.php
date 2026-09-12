<?php

namespace App\Console\Commands;

use App\Domains\SelfService\Enums\ServiceRequestStatus;
use App\Domains\SelfService\Models\HrServiceRequest;
use App\Domains\SelfService\Services\RequestEscalationService;
use Illuminate\Console\Command;

class ServiceSlaEvaluateCommand extends Command
{
    protected $signature = 'hcm:service-sla-evaluate {--tenant= : Specific Tenant UUID}';

    protected $description = 'Evaluate active HR service requests against response and resolution SLA deadlines and trigger escalations';

    public function handle(RequestEscalationService $escalationService): int
    {
        $tenantId = $this->option('tenant');
        $this->info("Starting HR Service SLA evaluation...");

        $query = HrServiceRequest::query()
            ->whereNotIn('status', [ServiceRequestStatus::RESOLVED->value, ServiceRequestStatus::CLOSED->value, ServiceRequestStatus::CANCELLED->value])
            ->whereHas('slaInstance', fn ($q) => $q->where('status', '!=', 'met'));

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        $requests = $query->with(['slaInstance'])->get();
        $this->info("Found {$requests->count()} active requests with SLA tracking.");

        $escalatedCount = 0;
        foreach ($requests as $req) {
            $escalation = $escalationService->evaluateRequest($req);
            if ($escalation) {
                $escalatedCount++;
            }
        }

        $this->info("Completed SLA evaluation. Processed {$escalatedCount} warnings/escalations.");

        return 0;
    }
}
