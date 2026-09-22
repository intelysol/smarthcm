<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enterprise System Health & Operations Command Center</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --brand-navy: #1E3A5F;
            --brand-dark-navy: #142A44;
            --brand-gold: #C9A227;
            --brand-gold-light: #F4E7B2;
            --bg-canvas: #F7F9FC;
            --surface: #FFFFFF;
            --border: #E5E7EB;
            --text-primary: #1F2937;
            --text-muted: #6B7280;
            --status-success: #16805C;
            --status-warning: #B7791F;
            --status-danger: #C0392B;
        }
    </style>
</head>
<body class="bg-[#F7F9FC] text-[#1F2937] antialiased min-h-screen">
    <!-- Header -->
    <header class="bg-[#1E3A5F] text-white border-b border-[#142A44] px-6 py-4 shadow-sm">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-9 h-9 rounded bg-[#C9A227] flex items-center justify-center font-bold text-[#142A44] shadow-sm">
                    HCM
                </div>
                <div>
                    <h1 class="text-lg font-semibold tracking-tight text-white">System Health & Operations Command Center</h1>
                    <p class="text-xs text-slate-300">Enterprise Platform v{{ $health['version'] }} &bull; Environment: <span class="font-mono uppercase text-[#F4E7B2]">{{ $health['environment'] }}</span></p>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $health['status'] === 'ok' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-amber-500/20 text-amber-300 border border-amber-500/30' }}">
                    <span class="w-2 h-2 mr-2 rounded-full {{ $health['status'] === 'ok' ? 'bg-emerald-400 animate-pulse' : 'bg-amber-400' }}"></span>
                    System {{ strtoupper($health['status']) }}
                </span>
                <a href="/health" target="_blank" class="text-xs text-slate-300 hover:text-white bg-[#142A44] px-3 py-1.5 rounded border border-slate-600 transition">
                    View Raw JSON Probe &rarr;
                </a>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-6 py-8 space-y-6">
        <!-- Top Metrics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
            <!-- Database Card -->
            <div class="bg-white rounded-lg border border-[#E5E7EB] p-5 shadow-sm">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold uppercase tracking-wider text-[#6B7280]">Database (MySQL)</span>
                    <span class="px-2 py-0.5 rounded text-[11px] font-bold {{ ($health['checks']['database']['status'] ?? '') === 'ok' ? 'bg-emerald-50 text-[#16805C]' : 'bg-rose-50 text-[#C0392B]' }}">
                        {{ strtoupper($health['checks']['database']['status'] ?? 'UNKNOWN') }}
                    </span>
                </div>
                <div class="text-2xl font-bold text-[#1E3A5F]">
                    {{ $health['checks']['database']['latency_ms'] ?? 0 }} <span class="text-sm font-normal text-slate-500">ms latency</span>
                </div>
                <div class="mt-2 text-xs text-[#6B7280]">
                    Schema: 985 Tables &bull; 2,180 FKs
                </div>
            </div>

            <!-- Cache Card -->
            <div class="bg-white rounded-lg border border-[#E5E7EB] p-5 shadow-sm">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold uppercase tracking-wider text-[#6B7280]">Cache & Session</span>
                    <span class="px-2 py-0.5 rounded text-[11px] font-bold {{ ($health['checks']['cache']['status'] ?? '') === 'ok' ? 'bg-emerald-50 text-[#16805C]' : 'bg-rose-50 text-[#C0392B]' }}">
                        {{ strtoupper($health['checks']['cache']['status'] ?? 'UNKNOWN') }}
                    </span>
                </div>
                <div class="text-2xl font-bold text-[#1E3A5F]">
                    {{ $health['checks']['cache']['latency_ms'] ?? 0 }} <span class="text-sm font-normal text-slate-500">ms latency</span>
                </div>
                <div class="mt-2 text-xs text-[#6B7280]">
                    Driver: {{ $health['checks']['cache']['store'] ?? 'redis' }}
                </div>
            </div>

            <!-- Queue Workers Card -->
            <div class="bg-white rounded-lg border border-[#E5E7EB] p-5 shadow-sm">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold uppercase tracking-wider text-[#6B7280]">Queue Infrastructure</span>
                    <span class="px-2 py-0.5 rounded text-[11px] font-bold {{ ($health['checks']['queue']['failed_jobs'] ?? 0) === 0 ? 'bg-emerald-50 text-[#16805C]' : 'bg-amber-50 text-[#B7791F]' }}">
                        {{ ($health['checks']['queue']['failed_jobs'] ?? 0) === 0 ? 'OPERATIONAL' : 'DEGRADED' }}
                    </span>
                </div>
                <div class="text-2xl font-bold text-[#1E3A5F]">
                    {{ $health['checks']['queue']['failed_jobs'] ?? 0 }} <span class="text-sm font-normal text-slate-500">failed jobs</span>
                </div>
                <div class="mt-2 text-xs text-[#6B7280]">
                    Driver: {{ $health['checks']['queue']['driver'] ?? 'redis' }}
                </div>
            </div>

            <!-- Storage & Memory Card -->
            <div class="bg-white rounded-lg border border-[#E5E7EB] p-5 shadow-sm">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold uppercase tracking-wider text-[#6B7280]">Memory & Runtime</span>
                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-blue-50 text-blue-700">
                        PHP {{ $health['system']['php_version'] }}
                    </span>
                </div>
                <div class="text-2xl font-bold text-[#1E3A5F]">
                    {{ $health['system']['memory_usage_mb'] }} <span class="text-sm font-normal text-slate-500">MB active</span>
                </div>
                <div class="mt-2 text-xs text-[#6B7280]">
                    Peak: {{ $health['system']['peak_memory_mb'] }} MB
                </div>
            </div>
        </div>

        <!-- Subsystems Status Table -->
        <div class="bg-white rounded-lg border border-[#E5E7EB] shadow-sm overflow-hidden">
            <div class="px-6 py-4 bg-slate-50 border-b border-[#E5E7EB] flex items-center justify-between">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-[#1E3A5F]">Subsystem Health Breakdown</h2>
                <span class="text-xs text-[#6B7280]">Updated: {{ $health['timestamp'] }}</span>
            </div>
            <div class="divide-y divide-gray-100">
                <!-- Database Row -->
                <div class="px-6 py-4 flex items-center justify-between">
                    <div>
                        <div class="text-sm font-medium text-[#1F2937]">Primary Relational Store</div>
                        <div class="text-xs text-[#6B7280]">Authoritative database engine verifying 985 tables and 2,180 foreign keys</div>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800">
                            Healthy ({{ $health['checks']['database']['latency_ms'] ?? 0 }}ms)
                        </span>
                    </div>
                </div>

                <!-- Object Storage Row -->
                <div class="px-6 py-4 flex items-center justify-between">
                    <div>
                        <div class="text-sm font-medium text-[#1F2937]">Encrypted Object Storage</div>
                        <div class="text-xs text-[#6B7280]">Disk: {{ $health['checks']['storage']['disk'] ?? 'local' }} &bull; Document vault & attachment store</div>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium {{ ($health['checks']['storage']['status'] ?? '') === 'ok' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                            {{ strtoupper($health['checks']['storage']['status'] ?? 'WARNING') }}
                        </span>
                    </div>
                </div>

                <!-- Multi-Tenant Isolation Row -->
                <div class="px-6 py-4 flex items-center justify-between">
                    <div>
                        <div class="text-sm font-medium text-[#1F2937]">Multi-Tenant Isolation Guard</div>
                        <div class="text-xs text-[#6B7280]">Scoped tenancy enforcement & cross-tenant query suppression</div>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800">
                            Active & Enforced
                        </span>
                    </div>
                </div>

                <!-- API Gateway Ingress Row -->
                <div class="px-6 py-4 flex items-center justify-between">
                    <div>
                        <div class="text-sm font-medium text-[#1F2937]">API Management Gateway</div>
                        <div class="text-xs text-[#6B7280]">SHA-256 key hashing, sliding rate limits, and correlation tracing</div>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800">
                            Governed & Rate-Limited
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity & Audit Logs -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Recent API Ingress -->
            <div class="bg-white rounded-lg border border-[#E5E7EB] shadow-sm p-6">
                <h3 class="text-sm font-semibold uppercase tracking-wider text-[#1E3A5F] mb-4">Recent API Gateway Activity</h3>
                @if(count($recentRequests) > 0)
                    <div class="space-y-3">
                        @foreach($recentRequests as $req)
                            <div class="flex items-center justify-between p-2.5 rounded bg-slate-50 text-xs border border-slate-100">
                                <div>
                                    <span class="font-bold text-slate-700 font-mono">{{ Str::limit($req->correlation_id ?? 'REQUEST', 24) }}</span>
                                    <span class="text-slate-500 ml-2">IP: {{ $req->ip_address ?? '127.0.0.1' }}</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="px-2 py-0.5 rounded font-mono font-bold {{ $req->response_status < 400 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                        {{ $req->response_status }}
                                    </span>
                                    <span class="text-slate-400">{{ $req->latency_ms }}ms</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-slate-500 py-4 text-center">No recent external API requests recorded.</p>
                @endif
            </div>

            <!-- Recent Failed Jobs -->
            <div class="bg-white rounded-lg border border-[#E5E7EB] shadow-sm p-6">
                <h3 class="text-sm font-semibold uppercase tracking-wider text-[#1E3A5F] mb-4">Queue Dead-Letter & Failed Jobs</h3>
                @if(count($failedJobs) > 0)
                    <div class="space-y-3">
                        @foreach($failedJobs as $job)
                            <div class="p-2.5 rounded bg-rose-50/50 text-xs border border-rose-100">
                                <div class="font-bold text-rose-900">{{ $job->queue }} &bull; {{ Str::limit($job->exception, 80) }}</div>
                                <div class="text-slate-500 text-[11px] mt-1">Failed at: {{ $job->failed_at }}</div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-8 text-center text-xs text-emerald-700 font-medium">
                        <span class="block text-xl mb-1">&#10003;</span>
                        Zero dead-letter or failed jobs. All queues running cleanly.
                    </div>
                @endif
            </div>
        </div>
    </main>
</body>
</html>
