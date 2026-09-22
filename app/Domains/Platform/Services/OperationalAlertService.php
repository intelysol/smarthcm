<?php

declare(strict_types=1);

namespace App\Domains\Platform\Services;

use App\Domains\Platform\Models\PlatformNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class OperationalAlertService
{
    public function __construct(private readonly HealthCheckService $healthService) {}

    public function evaluateAndAlert(): array
    {
        $alerts = [];
        $health = $this->healthService->getDetailedHealth();

        // 1. Check Database Health
        if (($health['checks']['database']['status'] ?? '') !== 'ok') {
            $alerts[] = $this->raiseAlert(
                'DATABASE_UNAVAILABLE',
                'CRITICAL',
                'Database health check failed: ' . ($health['checks']['database']['message'] ?? 'Connection error')
            );
        }

        // 2. Check Queue & Failed Jobs
        $failedJobsCount = $health['checks']['queue']['failed_jobs'] ?? 0;
        if ($failedJobsCount > 10) {
            $alerts[] = $this->raiseAlert(
                'HIGH_FAILED_JOBS_THRESHOLD',
                'WARNING',
                "Elevated failed background jobs detected: {$failedJobsCount} failed jobs in queue."
            );
        }

        // 3. Check Dead-Letters
        try {
            if (DB::getSchemaBuilder()->hasTable('integration_dead_letters')) {
                $deadLetterCount = DB::table('integration_dead_letters')->where('status', 'pending')->count();
                if ($deadLetterCount > 5) {
                    $alerts[] = $this->raiseAlert(
                        'DEAD_LETTER_ACCUMULATION',
                        'WARNING',
                        "Integration dead-letter queue has {$deadLetterCount} unresolved failed deliveries."
                    );
                }
            }
        } catch (Throwable $e) {
            // Non-blocking
        }

        // 4. Check Storage
        if (($health['checks']['storage']['status'] ?? '') !== 'ok') {
            $alerts[] = $this->raiseAlert(
                'STORAGE_UNAVAILABLE',
                'CRITICAL',
                'Primary object/file storage is not writable.'
            );
        }

        return $alerts;
    }

    private function raiseAlert(string $code, string $severity, string $message): array
    {
        $alertData = [
            'code' => $code,
            'severity' => $severity,
            'message' => $message,
            'timestamp' => now()->toIso8601String(),
        ];

        // Structured logging
        Log::channel('daily')->log(
            $severity === 'CRITICAL' ? 'critical' : 'warning',
            "[OPERATIONAL_ALERT] {$code}: {$message}",
            $alertData
        );

        // Record in platform notifications if table exists
        try {
            if (DB::getSchemaBuilder()->hasTable('platform_notifications')) {
                PlatformNotification::create([
                    'type' => 'operational_alert',
                    'title' => "System Alert: {$code}",
                    'body' => $message,
                    'severity' => strtolower($severity),
                    'metadata' => $alertData,
                    'created_at' => now(),
                ]);
            }
        } catch (Throwable $e) {
            // Non-blocking fallback to log
        }

        return $alertData;
    }
}
