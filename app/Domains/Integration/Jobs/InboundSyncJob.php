<?php

declare(strict_types=1);

namespace App\Domains\Integration\Jobs;

use App\Domains\Employee\DTOs\EmployeeData;
use App\Domains\Employee\Models\Employee;
use App\Domains\Employee\Services\EmployeeService;
use App\Domains\Integration\Models\IntegrationConnection;
use App\Domains\Integration\Models\IntegrationMapping;
use App\Domains\Integration\Models\IntegrationSyncRun;
use App\Domains\Integration\Services\CredentialManager;
use App\Domains\Integration\Services\DeadLetterService;
use App\Domains\Integration\Services\MappingEngine;
use Flow\Packages\Integrations\Infrastructure\Services\ConnectorRegistry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class InboundSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public array $params
    ) {}

    public function handle(
        ConnectorRegistry $registry,
        CredentialManager $credentialManager,
        MappingEngine $mappingEngine,
        EmployeeService $employeeService,
        DeadLetterService $deadLetterService
    ): void {
        $connectionId = $this->params['connection_id'] ?? null;
        $syncRunId = $this->params['sync_run_id'] ?? null;
        $entityType = $this->params['entity_type'] ?? 'employee';
        $directPayload = $this->params['payload'] ?? null;

        $connection = IntegrationConnection::with('connector')->find($connectionId);
        if (!$connection) {
            Log::error("InboundSyncJob: Connection [{$connectionId}] not found.");
            return;
        }

        $syncRun = $syncRunId ? IntegrationSyncRun::find($syncRunId) : null;
        if ($syncRun) {
            $syncRun->status = 'running';
            $syncRun->save();
        }

        $processed = 0;
        $failed = 0;

        try {
            // Find mapping for this connection and entity
            $mapping = IntegrationMapping::where('connection_id', $connectionId)->first();

            $records = [];
            if ($directPayload !== null) {
                // Event-based or single webhook payload
                $records = isset($directPayload[0]) && is_array($directPayload[0]) ? $directPayload : [$directPayload];
            } else {
                // Scheduled pull
                $connector = $registry->get($connection->connector->key);
                $credentials = $credentialManager->resolveCredentials($connection->id);
                $pullResult = $connector->pull(['entity' => $entityType], $credentials, $connection->configuration ?? []);

                if (!$pullResult->success) {
                    throw new \RuntimeException("Connector pull failed: " . $pullResult->errorMessage);
                }

                $records = is_array($pullResult->data) ? $pullResult->data : [];
            }

            foreach ($records as $rawRecord) {
                try {
                    $transformed = $mapping
                        ? $mappingEngine->transform($rawRecord, $mapping, 'inbound')
                        : $rawRecord;

                    $tenantId = $connection->tenant_id;

                    if ($entityType === 'employee') {
                        $this->processEmployeeRecord($tenantId, $transformed, $employeeService);
                    }

                    $processed++;
                } catch (Throwable $e) {
                    $failed++;
                    Log::warning("Error processing inbound record: " . $e->getMessage(), ['record' => $rawRecord]);
                }
            }

            if ($syncRun) {
                $syncRun->processed = $processed;
                $syncRun->failed = $failed;
                $syncRun->status = ($failed > 0 && $processed === 0) ? 'failed' : 'completed';
                $syncRun->completed_at = now();
                $syncRun->save();
            }
        } catch (Throwable $e) {
            if ($syncRun) {
                $syncRun->status = 'failed';
                $syncRun->error_message = $e->getMessage();
                $syncRun->completed_at = now();
                $syncRun->save();
            }

            $deadLetterService->recordDeadLetter(
                $connectionId,
                'inbound_sync',
                $this->params,
                $e->getMessage(),
                $this->attempts(),
                $connection->tenant_id
            );

            throw $e;
        }
    }

    protected function processEmployeeRecord(string $tenantId, array $data, EmployeeService $employeeService): Employee
    {
        $email = $data['official_email'] ?? $data['work_email'] ?? $data['email'] ?? null;

        // Check if employee already exists by official_email in tenant
        $existing = null;
        if ($email) {
            $existing = Employee::where('tenant_id', $tenantId)
                ->where('official_email', $email)
                ->first();
        }

        if ($existing) {
            // Update existing employee
            $existing->fill(array_filter([
                'first_name' => $data['first_name'] ?? $existing->first_name,
                'last_name' => $data['last_name'] ?? $existing->last_name,
                'employment_status' => $data['employment_status'] ?? $existing->employment_status,
                'department_id' => $data['department_id'] ?? $existing->department_id,
            ]));
            $existing->save();
            return $existing;
        }

        $companyId = $data['company_id'] ?? \App\Domains\Organization\Models\Company::where('tenant_id', $tenantId)->value('id');
        if (!$companyId) {
            $company = \App\Domains\Organization\Models\Company::firstOrCreate(
                ['tenant_id' => $tenantId],
                ['name' => 'Default Company', 'legal_name' => 'Default Company Ltd']
            );
            $companyId = $company->id;
        }

        // Create new employee via EmployeeService
        $attributes = [
            'company_id' => $companyId,
            'first_name' => $data['first_name'] ?? 'Unknown',
            'last_name' => $data['last_name'] ?? 'External',
            'official_email' => $email ?? ('ext_' . Str::random(8) . '@company.local'),
            'joining_date' => $data['hire_date'] ?? $data['joining_date'] ?? now()->toDateString(),
            'employment_status' => $data['employment_status'] ?? 'full_time',
            'department_id' => $data['department_id'] ?? null,
        ];

        $actorId = \App\Models\User::where('tenant_id', $tenantId)->value('id')
            ?? \App\Models\User::first()?->id;

        if (!$actorId) {
            $systemUser = \App\Models\User::firstOrCreate(
                ['email' => 'system.integration@smarthcm.local'],
                [
                    'tenant_id' => $tenantId,
                    'name' => 'Integration System',
                    'password' => bcrypt(Str::random(32)),
                ]
            );
            $actorId = $systemUser->id;
        }

        $employeeData = EmployeeData::fromArray($attributes, $tenantId, $actorId);
        return $employeeService->create($employeeData);
    }
}
