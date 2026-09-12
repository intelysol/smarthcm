<?php

namespace App\Console\Commands;

use App\Domains\Attendance\Models\AttendanceSession;
use App\Domains\Attendance\Services\LaborComplianceIntelligenceService;
use Illuminate\Console\Command;

class HcmTimeComplianceMonitorCommand extends Command
{
    protected $signature = 'hcm:time-compliance-monitor {--tenant= : Optional Tenant UUID filter}';
    protected $description = 'Evaluate labor compliance limits across unreviewed attendance sessions';

    public function handle(LaborComplianceIntelligenceService $service): int
    {
        $query = AttendanceSession::whereNotNull('actual_end_time')
            ->where('created_at', '>=', now()->subDays(2));

        if ($tenant = $this->option('tenant')) {
            $query->where('tenant_id', $tenant);
        }

        $sessions = $query->get();
        $this->info("Scanning {$sessions->count()} recent attendance sessions for labor compliance...");

        $totalViolations = 0;
        foreach ($sessions as $session) {
            $checks = $service->evaluateSessionCompliance($session);
            $totalViolations += $checks->count();
        }

        $this->info("Labor compliance evaluation complete. Total violations flagged: {$totalViolations}.");
        return self::SUCCESS;
    }
}