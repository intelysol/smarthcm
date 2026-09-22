<?php

declare(strict_types=1);

namespace App\Domains\Integration\Http\Controllers;

use App\Domains\Api\Models\ApiClient;
use App\Domains\Api\Models\ApiProduct;
use App\Domains\Api\Models\ApiRequestLog;
use App\Domains\Integration\Models\IntegrationConnection;
use App\Domains\Integration\Models\IntegrationConnector;
use App\Domains\Integration\Models\IntegrationDeadLetter;
use App\Domains\Integration\Models\IntegrationSyncRun;
use App\Domains\Integration\Models\WebhookSubscription;
use App\Domains\Integration\Services\DeadLetterService;
use App\Domains\Integration\Services\IntegrationAiAssistant;
use App\Domains\Integration\Services\IntegrationMonitoringService;
use App\Domains\Integration\Services\SyncEngine;
use Flow\Packages\Integrations\Infrastructure\Services\ConnectorRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IntegrationWebController
{
    public function __construct(
        protected ConnectorRegistry $registry,
        protected SyncEngine $syncEngine,
        protected DeadLetterService $deadLetterService,
        protected IntegrationMonitoringService $monitoringService,
        protected IntegrationAiAssistant $aiAssistant
    ) {}

    public function index(Request $request): View
    {
        // Sync registry connectors into database so catalog is fresh
        $this->registry->syncToDatabase();

        $connectors = IntegrationConnector::where('status', '!=', 'disabled')->get();
        $connections = IntegrationConnection::with('connector')->latest()->get();
        $syncRuns = IntegrationSyncRun::with('connection.connector')->latest()->take(20)->get();
        $deadLetters = IntegrationDeadLetter::with('connection.connector')->whereNull('replayed_at')->latest()->get();
        $webhooks = WebhookSubscription::latest()->get();
        $apiClients = ApiClient::with('keys')->latest()->get();
        $apiProducts = ApiProduct::with('endpoints')->latest()->get();
        $metrics = $this->monitoringService->getDashboardMetrics();

        return view('integrations.index', compact(
            'connectors',
            'connections',
            'syncRuns',
            'deadLetters',
            'webhooks',
            'apiClients',
            'apiProducts',
            'metrics'
        ));
    }

    public function triggerSync(Request $request, string $connectionId): RedirectResponse
    {
        $direction = $request->input('direction', 'inbound');
        $this->syncEngine->triggerScheduledSync($connectionId, $direction);

        return back()->with('success', 'Synchronization job triggered successfully.');
    }

    public function testConnection(string $connectionId): JsonResponse
    {
        $health = $this->monitoringService->checkConnectionHealth($connectionId);
        return response()->json($health);
    }

    public function replayDeadLetter(string $deadLetterId): RedirectResponse
    {
        $this->deadLetterService->replay($deadLetterId);
        return back()->with('success', 'Dead letter replayed successfully.');
    }

    public function diagnoseDeadLetter(string $deadLetterId): JsonResponse
    {
        $diagnosis = $this->aiAssistant->diagnoseDeadLetter($deadLetterId);
        return response()->json($diagnosis);
    }
}
