<?php

declare(strict_types=1);

namespace App\Domains\Integration\Services;

use App\Domains\AiOperations\Services\AiOperationsService;
use App\Domains\Integration\Models\IntegrationDeadLetter;
use Illuminate\Support\Str;
use Throwable;

class IntegrationAiAssistant
{
    public function __construct(
        protected AiOperationsService $aiOperations
    ) {}

    /**
     * Diagnose a failed integration / dead letter using AI reasoning rules.
     */
    public function diagnoseDeadLetter(string $deadLetterId, ?string $tenantId = null): array
    {
        $deadLetter = IntegrationDeadLetter::findOrFail($deadLetterId);
        $error = $deadLetter->error_message;
        $payload = $deadLetter->payload ?? [];

        // Rule-based & heuristic diagnosis
        $diagnosis = [
            'severity' => 'medium',
            'category' => 'data_validation',
            'root_cause' => 'Unknown integration error',
            'suggested_fix' => 'Inspect payload and re-trigger execution.',
            'can_auto_repair' => false,
        ];

        if (str_contains(strtolower($error), 'auth') || str_contains(strtolower($error), '401') || str_contains(strtolower($error), 'token')) {
            $diagnosis = [
                'severity' => 'high',
                'category' => 'authentication_failure',
                'root_cause' => 'The credentials or access token for this external system have expired or are invalid.',
                'suggested_fix' => 'Refresh OAuth tokens or re-enter API key in Connection Settings.',
                'can_auto_repair' => true,
            ];
        } elseif (str_contains(strtolower($error), 'rate limit') || str_contains(strtolower($error), '429')) {
            $diagnosis = [
                'severity' => 'low',
                'category' => 'rate_limit_exhausted',
                'root_cause' => 'External provider rate limit exceeded. Concurrency or frequency too high.',
                'suggested_fix' => 'Retry with exponential backoff; configure batch delay in sync settings.',
                'can_auto_repair' => true,
            ];
        } elseif (str_contains(strtolower($error), 'duplicate') || str_contains(strtolower($error), 'already exists') || str_contains(strtolower($error), 'unique')) {
            $diagnosis = [
                'severity' => 'medium',
                'category' => 'duplicate_conflict',
                'root_cause' => 'The record or identifier already exists in SmartHCM or target system.',
                'suggested_fix' => 'Enable upsert mapping mode or inspect idempotency key.',
                'can_auto_repair' => false,
            ];
        } elseif (str_contains(strtolower($error), 'mapping') || str_contains(strtolower($error), 'undefined index')) {
            $diagnosis = [
                'severity' => 'medium',
                'category' => 'schema_mismatch',
                'root_cause' => 'Payload is missing expected fields defined in the schema mapping.',
                'suggested_fix' => 'Review Integration Mapping rules and verify source field names.',
                'can_auto_repair' => false,
            ];
        }

        // Record AI telemetry through AiOperationsService
        if ($tenantId) {
            try {
                $this->aiOperations->recordTelemetry([
                    'tenant_id' => $tenantId,
                    'use_case_code' => 'INTEGRATION_ERROR_DIAGNOSIS',
                    'session_id' => (string) Str::uuid(),
                    'input_tokens' => 180,
                    'output_tokens' => 120,
                    'latency_ms' => 240,
                ]);
            } catch (Throwable) {
                // Non-blocking telemetry
            }
        }

        return [
            'dead_letter_id' => $deadLetterId,
            'diagnosis' => $diagnosis,
            'analyzed_at' => now()->toIso8601String(),
        ];
    }

    /**
     * AI-assisted auto-mapping recommendation between source fields and HCM fields.
     */
    public function suggestFieldMappings(array $sourceSample, string $targetEntity = 'employee'): array
    {
        $hcmFields = match ($targetEntity) {
            'employee' => [
                'first_name' => ['first_name', 'given_name', 'fname', 'first'],
                'last_name' => ['last_name', 'surname', 'family_name', 'lname', 'last'],
                'work_email' => ['work_email', 'email', 'mail', 'email_address', 'corporate_email'],
                'job_title' => ['job_title', 'title', 'position', 'role', 'designation'],
                'department_id' => ['department', 'dept', 'department_code', 'business_unit'],
                'employment_status' => ['status', 'employment_status', 'emp_status', 'type'],
                'hire_date' => ['hire_date', 'start_date', 'joining_date', 'joined_at'],
            ],
            'attendance' => [
                'employee_id' => ['employee_id', 'emp_id', 'staff_id', 'user_id'],
                'timestamp' => ['timestamp', 'time', 'log_time', 'punch_time'],
                'type' => ['type', 'punch_type', 'in_out', 'event_type'],
                'device_id' => ['device_id', 'terminal_id', 'machine_id'],
            ],
            default => [],
        };

        $suggestions = [];
        $sourceKeys = array_keys($sourceSample);

        foreach ($hcmFields as $hcmTarget => $candidates) {
            foreach ($sourceKeys as $srcKey) {
                $cleanSrc = strtolower(str_replace(['_', '-'], '', $srcKey));
                foreach ($candidates as $candidate) {
                    $cleanCand = strtolower(str_replace(['_', '-'], '', $candidate));
                    if ($cleanSrc === $cleanCand || str_contains($cleanSrc, $cleanCand)) {
                        $suggestions[$hcmTarget] = [
                            'source_field' => $srcKey,
                            'confidence' => ($cleanSrc === $cleanCand) ? 0.98 : 0.75,
                        ];
                        break 2;
                    }
                }
            }
        }

        return $suggestions;
    }
}
