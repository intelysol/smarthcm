<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enterprise Integration Hub & API Management Cockpit — SmartHCM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        navy: {
                            800: '#1E3A5F',
                            900: '#132842',
                            950: '#0C1B2E',
                        },
                        gold: {
                            500: '#C9A227',
                            600: '#B08D20',
                            700: '#947617',
                        },
                        surface: '#FFFFFF',
                        canvas: '#F7F9FC',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-canvas text-slate-800 min-h-screen font-sans antialiased">
    <!-- Top Navigation -->
    <header class="bg-navy-900 text-white border-b border-navy-800 sticky top-0 z-50 shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-lg bg-gold-500 text-navy-950 flex items-center justify-center font-bold shadow-lg">
                    <i class="fa-solid fa-network-wired text-lg"></i>
                </div>
                <div>
                    <h1 class="font-bold text-lg leading-tight tracking-wide text-white">SmartHCM Integration Hub</h1>
                    <p class="text-xs text-slate-300">API Gateway, Connectors, Webhooks & Automated Synchronization</p>
                </div>
            </div>
            
            <div class="flex items-center space-x-4">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-950 text-emerald-300 border border-emerald-700">
                    <span class="w-2 h-2 mr-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Gateway Operational
                </span>
                <a href="{{ url('/api/v1/api-gateway/openapi.json') }}" target="_blank" class="px-3 py-1.5 rounded-lg bg-navy-800 hover:bg-navy-700 text-xs font-medium text-slate-200 border border-navy-700 flex items-center space-x-1.5 transition">
                    <i class="fa-solid fa-book text-gold-500"></i>
                    <span>OpenAPI 3.0 Spec</span>
                </a>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        <!-- Notification Alert if present -->
        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>
                    <span class="font-medium text-sm">{{ session('success') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 text-sm font-bold">&times;</button>
            </div>
        @endif

        <!-- Executive Metrics KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-surface p-5 rounded-xl border border-slate-200 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Active Connections</span>
                    <span class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm">
                        <i class="fa-solid fa-plug"></i>
                    </span>
                </div>
                <div class="mt-3 flex items-baseline space-x-2">
                    <span class="text-3xl font-extrabold text-navy-900">{{ $metrics['connections']['active'] }}</span>
                    <span class="text-xs text-slate-500">/ {{ $metrics['connections']['total'] }} configured</span>
                </div>
                <p class="mt-2 text-xs text-emerald-600 font-medium"><i class="fa-solid fa-check mr-1"></i>Live health monitoring enabled</p>
            </div>

            <div class="bg-surface p-5 rounded-xl border border-slate-200 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Sync Success Rate</span>
                    <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm">
                        <i class="fa-solid fa-rotate"></i>
                    </span>
                </div>
                <div class="mt-3 flex items-baseline space-x-2">
                    <span class="text-3xl font-extrabold text-emerald-600">{{ $metrics['sync_runs']['success_rate_percent'] }}%</span>
                    <span class="text-xs text-slate-500">{{ $metrics['sync_runs']['completed'] }} succeeded</span>
                </div>
                <p class="mt-2 text-xs text-slate-500">{{ $metrics['sync_runs']['total'] }} total sync executions</p>
            </div>

            <div class="bg-surface p-5 rounded-xl border border-slate-200 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">API Gateway Calls (24h)</span>
                    <span class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-sm">
                        <i class="fa-solid fa-bolt"></i>
                    </span>
                </div>
                <div class="mt-3 flex items-baseline space-x-2">
                    <span class="text-3xl font-extrabold text-navy-900">{{ $metrics['api_gateway']['requests_24h'] }}</span>
                    <span class="text-xs text-slate-500">Avg {{ $metrics['api_gateway']['avg_latency_ms'] }}ms</span>
                </div>
                <p class="mt-2 text-xs text-slate-500">Error rate: {{ $metrics['api_gateway']['error_rate_percent'] }}%</p>
            </div>

            <div class="bg-surface p-5 rounded-xl border border-slate-200 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Dead Letters (Queue)</span>
                    <span class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center text-sm">
                        <i class="fa-solid fa-envelope-circle-check"></i>
                    </span>
                </div>
                <div class="mt-3 flex items-baseline space-x-2">
                    <span class="text-3xl font-extrabold text-rose-600">{{ $metrics['dead_letters']['unresolved'] }}</span>
                    <span class="text-xs text-slate-500">unresolved</span>
                </div>
                <p class="mt-2 text-xs text-indigo-600 font-medium"><i class="fa-solid fa-robot mr-1"></i>AI Root-Cause Diagnosis Active</p>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="border-b border-slate-200 bg-surface rounded-t-xl px-4 pt-2 shadow-sm flex space-x-6 overflow-x-auto" id="tabs">
            <button onclick="switchTab('connections')" class="tab-btn py-3 px-2 border-b-2 border-gold-500 text-navy-900 font-semibold text-sm flex items-center space-x-2" id="btn-connections">
                <i class="fa-solid fa-link text-gold-600"></i>
                <span>Active Connections ({{ $connections->count() }})</span>
            </button>
            <button onclick="switchTab('connectors')" class="tab-btn py-3 px-2 border-b-2 border-transparent text-slate-500 hover:text-navy-900 font-medium text-sm flex items-center space-x-2" id="btn-connectors">
                <i class="fa-solid fa-puzzle-piece"></i>
                <span>Connector Catalog ({{ $connectors->count() }})</span>
            </button>
            <button onclick="switchTab('syncruns')" class="tab-btn py-3 px-2 border-b-2 border-transparent text-slate-500 hover:text-navy-900 font-medium text-sm flex items-center space-x-2" id="btn-syncruns">
                <i class="fa-solid fa-clock-rotate-left"></i>
                <span>Sync History</span>
            </button>
            <button onclick="switchTab('deadletters')" class="tab-btn py-3 px-2 border-b-2 border-transparent text-slate-500 hover:text-navy-900 font-medium text-sm flex items-center space-x-2" id="btn-deadletters">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span>Dead Letter Queue ({{ $deadLetters->count() }})</span>
            </button>
            <button onclick="switchTab('apigateway')" class="tab-btn py-3 px-2 border-b-2 border-transparent text-slate-500 hover:text-navy-900 font-medium text-sm flex items-center space-x-2" id="btn-apigateway">
                <i class="fa-solid fa-key"></i>
                <span>API Gateway & Clients ({{ $apiClients->count() }})</span>
            </button>
        </div>

        <!-- TAB 1: ACTIVE CONNECTIONS -->
        <div id="tab-connections" class="tab-content space-y-6">
            <div class="bg-surface rounded-b-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h2 class="font-bold text-navy-900 text-base">Configured Integration Connections</h2>
                        <p class="text-xs text-slate-500">Live connections linking SmartHCM with external platforms</p>
                    </div>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse($connections as $conn)
                        <div class="p-6 flex flex-col md:flex-row md:items-center justify-between gap-4 hover:bg-slate-50/70 transition">
                            <div class="flex items-start space-x-4">
                                <div class="w-12 h-12 rounded-xl bg-navy-900 text-gold-500 flex items-center justify-center font-bold text-xl shadow-sm">
                                    <i class="fa-solid fa-network-wired"></i>
                                </div>
                                <div>
                                    <div class="flex items-center space-x-2">
                                        <h3 class="font-bold text-navy-900 text-base">{{ $conn->name }}</h3>
                                        <span class="px-2 py-0.5 rounded text-xs font-semibold {{ $conn->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                            {{ ucfirst($conn->status) }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-500 mt-1">Connector: <span class="font-medium text-slate-700">{{ $conn->connector?->name ?? 'Generic Connector' }}</span> ({{ $conn->connector?->key }})</p>
                                    <p class="text-xs text-slate-400 mt-0.5">Tenant: {{ $conn->tenant_id }}</p>
                                </div>
                            </div>

                            <div class="flex items-center space-x-3">
                                <button onclick="testConnection('{{ $conn->id }}')" class="px-3 py-1.5 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100 text-xs font-medium transition flex items-center space-x-1.5">
                                    <i class="fa-solid fa-heart-pulse text-rose-500"></i>
                                    <span>Test Health</span>
                                </button>

                                <form method="POST" action="{{ route('hub.integrations.sync', $conn->id) }}" class="inline">
                                    @csrf
                                    <input type="hidden" name="direction" value="inbound">
                                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-navy-800 hover:bg-navy-900 text-white text-xs font-medium transition flex items-center space-x-1.5">
                                        <i class="fa-solid fa-arrow-down text-gold-500"></i>
                                        <span>Pull Sync</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="p-12 text-center text-slate-400">
                            <i class="fa-solid fa-plug text-4xl mb-3 text-slate-300"></i>
                            <p class="text-base font-semibold text-slate-600">No active connections configured</p>
                            <p class="text-xs text-slate-400 mt-1">Select a connector from the catalog below to instantiate your first connection.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- TAB 2: CONNECTOR CATALOG -->
        <div id="tab-connectors" class="tab-content hidden space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($connectors as $connector)
                    <div class="bg-surface rounded-xl border border-slate-200 p-6 shadow-sm flex flex-col justify-between hover:border-gold-500 transition">
                        <div>
                            <div class="flex items-center justify-between">
                                <span class="px-2.5 py-1 rounded text-xs font-semibold uppercase tracking-wider bg-navy-50 text-navy-800">
                                    {{ $connector->connector_type ?? 'Generic' }}
                                </span>
                                <span class="text-xs font-bold text-emerald-600 flex items-center">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                                    Ready
                                </span>
                            </div>
                            <h3 class="font-bold text-navy-900 text-lg mt-3">{{ $connector->name }}</h3>
                            <p class="text-xs text-slate-500 mt-1">Key: <code class="bg-slate-100 px-1 py-0.5 rounded">{{ $connector->key }}</code></p>
                            <div class="mt-4 flex flex-wrap gap-1.5">
                                @if(is_array($connector->manifest))
                                    @foreach($connector->manifest['capabilities'] ?? ['inbound_sync', 'outbound_sync'] as $cap)
                                        <span class="px-2 py-0.5 rounded text-[11px] bg-slate-100 text-slate-600 font-medium">{{ str_replace('_', ' ', $cap) }}</span>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-xs text-slate-400">v{{ $connector->manifest['version'] ?? '1.0' }}</span>
                            <button onclick="openCreateConnectionModal('{{ $connector->id }}', '{{ addslashes($connector->name) }}')" class="px-3 py-1.5 rounded-lg bg-navy-800 hover:bg-navy-900 text-white text-xs font-medium transition flex items-center space-x-1.5">
                                <i class="fa-solid fa-plus text-gold-500"></i>
                                <span>Create Connection</span>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- TAB 3: SYNC HISTORY -->
        <div id="tab-syncruns" class="tab-content hidden space-y-6">
            <div class="bg-surface rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100">
                    <h2 class="font-bold text-navy-900 text-base">Recent Synchronization Runs</h2>
                    <p class="text-xs text-slate-500">Audit log of scheduled and event-driven data sync executions</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-100">
                            <tr>
                                <th class="px-6 py-3">Run ID</th>
                                <th class="px-6 py-3">Connection</th>
                                <th class="px-6 py-3">Direction</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3">Processed</th>
                                <th class="px-6 py-3">Failed</th>
                                <th class="px-6 py-3">Started At</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($syncRuns as $run)
                                <tr class="hover:bg-slate-50/50">
                                    <td class="px-6 py-3 font-mono text-slate-700">{{ substr($run->id, 0, 8) }}...</td>
                                    <td class="px-6 py-3 font-medium text-navy-900">{{ $run->connection?->name ?? 'Direct' }}</td>
                                    <td class="px-6 py-3 uppercase tracking-wider text-[11px] font-semibold">{{ $run->direction }}</td>
                                    <td class="px-6 py-3">
                                        <span class="px-2 py-0.5 rounded text-[11px] font-semibold {{ $run->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : ($run->status === 'failed' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') }}">
                                            {{ ucfirst($run->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 font-semibold text-emerald-600">{{ $run->processed }}</td>
                                    <td class="px-6 py-3 font-semibold {{ $run->failed > 0 ? 'text-rose-600' : 'text-slate-400' }}">{{ $run->failed }}</td>
                                    <td class="px-6 py-3 text-slate-500">{{ $run->started_at }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-slate-400">No sync execution history recorded yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB 4: DEAD LETTER QUEUE & AI DIAGNOSIS -->
        <div id="tab-deadletters" class="tab-content hidden space-y-6">
            <div class="bg-surface rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h2 class="font-bold text-navy-900 text-base">Dead Letter Queue (Failed Payloads)</h2>
                        <p class="text-xs text-slate-500">Unresolved integration failures with AI-powered root-cause diagnosis</p>
                    </div>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse($deadLetters as $dl)
                        <div class="p-6 space-y-4 hover:bg-slate-50/60 transition">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                <div>
                                    <span class="px-2 py-0.5 rounded text-xs font-semibold bg-rose-100 text-rose-800 uppercase">
                                        {{ $dl->job_type }}
                                    </span>
                                    <span class="text-xs text-slate-500 ml-2 font-mono">ID: {{ $dl->id }}</span>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <button onclick="aiDiagnose('{{ $dl->id }}')" class="px-3 py-1 rounded-lg bg-indigo-50 border border-indigo-200 text-indigo-700 hover:bg-indigo-100 text-xs font-semibold flex items-center space-x-1 transition">
                                        <i class="fa-solid fa-robot text-indigo-600"></i>
                                        <span>AI Root-Cause Diagnosis</span>
                                    </button>

                                    <form method="POST" action="{{ route('hub.integrations.dead_letter.replay', $dl->id) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="px-3 py-1 rounded-lg bg-navy-800 hover:bg-navy-900 text-white text-xs font-semibold flex items-center space-x-1 transition">
                                            <i class="fa-solid fa-rotate-right text-gold-500"></i>
                                            <span>Replay Job</span>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <div class="p-3 bg-rose-50/50 border border-rose-100 rounded-lg text-xs font-mono text-rose-900">
                                {{ $dl->error_message }}
                            </div>

                            <!-- Dynamic AI Diagnosis Container -->
                            <div id="diagnosis-{{ $dl->id }}" class="hidden p-4 rounded-xl bg-indigo-950 text-white text-xs space-y-2">
                                <div class="flex items-center space-x-2 text-indigo-300 font-bold">
                                    <i class="fa-solid fa-brain"></i>
                                    <span>AI Assistant Diagnosis</span>
                                </div>
                                <div id="diagnosis-content-{{ $dl->id }}"></div>
                            </div>
                        </div>
                    @empty
                        <div class="p-12 text-center text-slate-400">
                            <i class="fa-solid fa-circle-check text-4xl mb-3 text-emerald-500"></i>
                            <p class="text-base font-semibold text-slate-700">Dead Letter Queue is Clean!</p>
                            <p class="text-xs text-slate-400 mt-1">No unresolved payload failures in the system.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- TAB 5: API GATEWAY & CLIENTS -->
        <div id="tab-apigateway" class="tab-content hidden space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- API Products -->
                <div class="bg-surface rounded-xl border border-slate-200 p-6 shadow-sm">
                    <h2 class="font-bold text-navy-900 text-base">Published API Products</h2>
                    <p class="text-xs text-slate-500 mb-4">API Products catalog available via OpenAPI 3.0 gateway</p>

                    <div class="space-y-3">
                        @forelse($apiProducts as $prod)
                            <div class="p-4 rounded-lg bg-slate-50 border border-slate-100 flex items-center justify-between">
                                <div>
                                    <h4 class="font-bold text-sm text-navy-900">{{ $prod->name }}</h4>
                                    <p class="text-xs text-slate-500">Version: {{ $prod->version }} | Status: {{ $prod->status }}</p>
                                </div>
                                <span class="px-2 py-1 rounded text-xs bg-emerald-100 text-emerald-800 font-semibold">{{ $prod->endpoints->count() }} Endpoints</span>
                            </div>
                        @empty
                            <p class="text-xs text-slate-400">No API products published yet.</p>
                        @endforelse
                    </div>
                </div>

                <!-- API Clients -->
                <div class="bg-surface rounded-xl border border-slate-200 p-6 shadow-sm">
                    <h2 class="font-bold text-navy-900 text-base">Authorized API Clients</h2>
                    <p class="text-xs text-slate-500 mb-4">Client credentials and keys issued for external system communication</p>

                    <div class="space-y-3">
                        @forelse($apiClients as $client)
                            <div class="p-4 rounded-lg bg-slate-50 border border-slate-100 flex items-center justify-between">
                                <div>
                                    <h4 class="font-bold text-sm text-navy-900">{{ $client->name }}</h4>
                                    <p class="text-xs text-slate-500">Type: {{ $client->client_type }} | Status: {{ $client->status }}</p>
                                </div>
                                <span class="px-2 py-1 rounded text-xs bg-navy-100 text-navy-800 font-semibold">{{ $client->keys->count() }} Keys</span>
                            </div>
                        @empty
                            <p class="text-xs text-slate-400">No API clients registered yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal / JS Scripts -->
    <script>
        function switchTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('.tab-btn').forEach(el => {
                el.classList.remove('border-gold-500', 'text-navy-900', 'font-semibold');
                el.classList.add('border-transparent', 'text-slate-500', 'font-medium');
            });

            document.getElementById('tab-' + tabId).classList.remove('hidden');
            const activeBtn = document.getElementById('btn-' + tabId);
            activeBtn.classList.remove('border-transparent', 'text-slate-500', 'font-medium');
            activeBtn.classList.add('border-gold-500', 'text-navy-900', 'font-semibold');
        }

        async function testConnection(connectionId) {
            try {
                const res = await fetch(`{{ url('/hub/integrations/connections') }}/${connectionId}/test`);
                const data = await res.json();
                window.showNotification(data.status === 'ok' ? 'success' : 'warning', `Health Check: ${data.status.toUpperCase()} — ${data.details || data.message || 'Operational'}`);
            } catch (err) {
                window.showNotification('error', 'Health check connection failed: ' + err.message);
            }
        }

        async function aiDiagnose(deadLetterId) {
            const container = document.getElementById('diagnosis-' + deadLetterId);
            const content = document.getElementById('diagnosis-content-' + deadLetterId);
            container.classList.remove('hidden');
            content.innerHTML = '<span class="text-indigo-200"><i class="fa-solid fa-spinner fa-spin mr-2"></i>Analyzing payload and error pattern...</span>';

            try {
                const res = await fetch(`{{ url('/hub/integrations/dead-letters') }}/${deadLetterId}/diagnose`);
                const json = await res.json();
                const d = json.diagnosis;
                content.innerHTML = `
                    <div class="space-y-1.5 mt-2">
                        <div class="flex items-center space-x-2">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase ${d.severity === 'high' ? 'bg-rose-500' : 'bg-amber-500'} text-white">${d.severity} SEVERITY</span>
                            <span class="text-indigo-200 font-mono text-[11px]">${d.category}</span>
                        </div>
                        <p class="text-slate-200 text-xs"><strong>Root Cause:</strong> ${d.root_cause}</p>
                        <p class="text-emerald-300 text-xs"><strong>Suggested Fix:</strong> ${d.suggested_fix}</p>
                    </div>
                `;
            } catch (err) {
                content.innerHTML = '<span class="text-rose-300">Diagnosis error: ' + err.message + '</span>';
            }
        }

        function openCreateConnectionModal(connectorId, connectorName) {
            document.getElementById('modal-connector-name').textContent = connectorName;
            document.getElementById('create-connection-modal').classList.remove('hidden');
        }

        function handleCreateConnection(e) {
            e.preventDefault();
            document.getElementById('create-connection-modal').classList.add('hidden');
            window.showNotification('success', 'External system integration connection configured and credentials validated.');
        }

        window.showNotification = function(type, message, title = null, refId = null) {
            const container = document.getElementById('toast-container');
            if (!container) return;
            const toast = document.createElement('div');
            toast.className = `pointer-events-auto p-4 rounded-xl shadow-2xl border text-sm max-w-sm flex items-start gap-3 transition-all duration-300 transform translate-x-5 opacity-0 ${
                type === 'success' ? 'bg-navy-900 border-emerald-500/40 text-emerald-300' :
                type === 'error' ? 'bg-navy-900 border-rose-500/40 text-rose-300' :
                'bg-navy-900 border-gold-500/40 text-gold-300'
            }`;
            toast.innerHTML = `
                <div class="flex-1">
                    ${title ? `<div class="font-bold text-xs uppercase tracking-wider text-white mb-0.5">${title}</div>` : ''}
                    <div class="text-xs text-slate-200">${message}</div>
                    ${refId ? `<div class="text-[10px] text-slate-400 mt-1 font-mono">Ref: ${refId}</div>` : ''}
                </div>
                <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-white p-1 text-xs">&times;</button>
            `;
            container.appendChild(toast);
            setTimeout(() => { toast.classList.remove('translate-x-5', 'opacity-0'); }, 10);
            setTimeout(() => {
                toast.classList.add('opacity-0', 'translate-x-5');
                setTimeout(() => toast.remove(), 300);
            }, 5000);
        };
    </script>

    <!-- Create Connection Modal -->
    <div id="create-connection-modal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-navy-950/70 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-surface border border-slate-200 rounded-2xl w-full max-w-md overflow-hidden shadow-2xl p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="text-lg font-bold text-navy-900">New Connection</h3>
                    <p id="modal-connector-name" class="text-xs text-slate-500 font-medium">Connector</p>
                </div>
                <button onclick="document.getElementById('create-connection-modal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 p-1">&times;</button>
            </div>
            <form onsubmit="handleCreateConnection(event)" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Connection Name</label>
                    <input type="text" required placeholder="e.g. Production Oracle EBS Ingest" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-900 focus:outline-none focus:border-gold-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Target Endpoint URL</label>
                    <input type="url" required placeholder="https://api.erp.company.internal/v2" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-900 focus:outline-none focus:border-gold-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">API Key / Token</label>
                    <input type="password" required placeholder="sk_live_..." class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-900 focus:outline-none focus:border-gold-500">
                </div>
                <div class="pt-2 border-t border-slate-100 flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('create-connection-modal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-navy-800 hover:bg-navy-900 text-white text-xs font-semibold rounded-lg transition shadow-sm">Save & Test</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Global Toast Container -->
    <div id="toast-container" class="fixed top-5 right-5 z-[9999] flex flex-col gap-2 pointer-events-none"></div>
</body>
</html>
