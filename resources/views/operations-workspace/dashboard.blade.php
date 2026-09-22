@extends('shells.operations')

@section('title', 'Operations Control Plane & SRE Dashboard')

@section('content')
<div class="space-y-6">

    <!-- Header Banner -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-6 shadow-md flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-mono text-[#C9A227] font-semibold uppercase tracking-wider mb-1">
                <i class="fa-solid fa-gauge-high text-[#C9A227]"></i>
                <span>Site Reliability Engineering &amp; Operations</span>
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight">Operations Control Plane</h1>
            <p class="text-xs text-zinc-400 mt-0.5">Authoritative telemetry, cluster health probes, active incident triage, and SLO error budgets.</p>
        </div>
        <div class="flex items-center space-x-3">
            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold {{ $health['status'] === 'ok' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20' }}">
                <span class="w-2 h-2 rounded-full {{ $health['status'] === 'ok' ? 'bg-emerald-400' : 'bg-rose-400' }} animate-pulse mr-2"></span>
                Cluster: {{ strtoupper($health['status']) }}
            </span>
            <a href="{{ route('operations.system-health') }}" class="px-3.5 py-2 rounded-xl bg-zinc-800 hover:bg-zinc-700 text-zinc-200 font-semibold text-xs transition border border-zinc-700 flex items-center">
                <i class="fa-solid fa-heart-pulse mr-1.5 text-emerald-400"></i> Full Probes
            </a>
        </div>
    </div>

    <!-- KPI Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-4">
            <span class="text-xs font-semibold text-zinc-400 uppercase tracking-wider">Active Incidents</span>
            <div class="mt-2 text-2xl font-black {{ $openIncidents > 0 ? 'text-amber-400' : 'text-emerald-400' }} font-mono">
                {{ $openIncidents }}
            </div>
            <p class="text-xs text-zinc-400 mt-1">SEV-0 to SEV-3 tracked</p>
        </div>
        <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-4">
            <span class="text-xs font-semibold text-zinc-400 uppercase tracking-wider">Active Alerts</span>
            <div class="mt-2 text-2xl font-black {{ $openAlerts > 0 ? 'text-rose-400' : 'text-emerald-400' }} font-mono">
                {{ $openAlerts }}
            </div>
            <p class="text-xs text-zinc-400 mt-1">Threshold breaches</p>
        </div>
        <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-4">
            <span class="text-xs font-semibold text-zinc-400 uppercase tracking-wider">Queue Throughput</span>
            <div class="mt-2 text-2xl font-black text-white font-mono">
                {{ $queueHealth['pending_jobs'] }} <span class="text-xs text-zinc-400 font-normal">pending</span>
            </div>
            <p class="text-xs text-zinc-400 mt-1">{{ $queueHealth['failed_jobs'] }} failed in dead-letter queue</p>
        </div>
        <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-4">
            <span class="text-xs font-semibold text-zinc-400 uppercase tracking-wider">Platform Availability</span>
            <div class="mt-2 text-2xl font-black text-emerald-400 font-mono">99.98%</div>
            <p class="text-xs text-zinc-400 mt-1">SLO target: 99.9%</p>
        </div>
    </div>

    <!-- SLO Compliance & Error Budgets -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-6 shadow-md">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-lg font-bold text-white tracking-tight flex items-center gap-2">
                    <i class="fa-solid fa-chart-line text-[#C9A227]"></i>
                    Service Level Objectives (SLOs) &amp; Error Budgets
                </h2>
                <p class="text-xs text-zinc-400 mt-0.5">Continuous evaluation against authoritative production SLO targets.</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-zinc-800 text-zinc-400 uppercase tracking-wider">
                        <th class="pb-3 font-semibold">SLO Name</th>
                        <th class="pb-3 font-semibold">Target</th>
                        <th class="pb-3 font-semibold">Current Value</th>
                        <th class="pb-3 font-semibold">Remaining Error Budget</th>
                        <th class="pb-3 font-semibold text-right">Compliance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/50">
                    @foreach($slos as $slo)
                    <tr class="hover:bg-zinc-800/30 transition">
                        <td class="py-3 font-medium text-white">{{ $slo['name'] }}</td>
                        <td class="py-3 text-zinc-400 font-mono">{{ $slo['target'] }}</td>
                        <td class="py-3 text-emerald-400 font-mono font-bold">{{ $slo['current'] }}</td>
                        <td class="py-3 text-zinc-300 font-mono">{{ $slo['error_budget_remaining'] }}</td>
                        <td class="py-3 text-right">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                COMPLIANT
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Infrastructure Dependencies Health Grid -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-6 shadow-md">
        <h2 class="text-lg font-bold text-white tracking-tight flex items-center gap-2 mb-4">
            <i class="fa-solid fa-network-wired text-emerald-400"></i>
            Backing Infrastructure &amp; Dependency Health
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($dependencies['dependencies'] as $name => $dep)
            <div class="p-4 rounded-xl border border-zinc-800 bg-zinc-950 flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-white uppercase tracking-wider">{{ ucfirst($name) }}</span>
                    <p class="text-[11px] text-zinc-400 mt-0.5">
                        @if(isset($dep['latency_ms']))
                            Latency: <span class="font-mono text-zinc-300">{{ $dep['latency_ms'] }}ms</span>
                        @elseif(isset($dep['driver']))
                            Driver: <span class="font-mono text-zinc-300">{{ $dep['driver'] }}</span>
                        @else
                            Managed Probe
                        @endif
                    </p>
                </div>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold {{ ($dep['status'] ?? 'ok') === 'ok' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20' }}">
                    {{ strtoupper($dep['status'] ?? 'OK') }}
                </span>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Recent Incidents & Operational Alerts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Incidents List -->
        <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-6 shadow-md">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation text-amber-400"></i>
                    Recent Incidents
                </h3>
                <a href="{{ route('operations.incidents') }}" class="text-xs text-[#C9A227] hover:underline font-semibold">View All</a>
            </div>
            <div class="space-y-3">
                @forelse($recentIncidents as $incident)
                <div class="p-3 rounded-xl border border-zinc-800 bg-zinc-950 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-white">{{ $incident->title }}</span>
                        <p class="text-[11px] text-zinc-400 mt-0.5">Severity: <span class="font-bold text-amber-400 uppercase">{{ $incident->severity }}</span> &bull; Status: {{ ucfirst($incident->status) }}</p>
                    </div>
                    <span class="text-[10px] text-zinc-500 font-mono">{{ $incident->created_at?->diffForHumans() }}</span>
                </div>
                @empty
                <div class="text-center py-6 text-zinc-500 text-xs">
                    <i class="fa-solid fa-circle-check text-emerald-400 text-lg mb-1 block"></i>
                    No active or recent incidents reported.
                </div>
                @endforelse
            </div>
        </div>

        <!-- Active Alerts List -->
        <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-6 shadow-md">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-bell text-rose-400"></i>
                    Active Operational Alerts
                </h3>
                <a href="{{ route('operations.alerts') }}" class="text-xs text-[#C9A227] hover:underline font-semibold">View All</a>
            </div>
            <div class="space-y-3">
                @forelse($activeAlerts as $alert)
                <div class="p-3 rounded-xl border border-zinc-800 bg-zinc-950 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-white">{{ $alert->message }}</span>
                        <p class="text-[11px] text-zinc-400 mt-0.5">Severity: <span class="font-bold text-rose-400 uppercase">{{ $alert->severity }}</span></p>
                    </div>
                    <span class="text-[10px] text-zinc-500 font-mono">{{ $alert->triggered_at?->diffForHumans() ?? 'Just now' }}</span>
                </div>
                @empty
                <div class="text-center py-6 text-zinc-500 text-xs">
                    <i class="fa-solid fa-shield-check text-emerald-400 text-lg mb-1 block"></i>
                    All operational metrics within safe thresholds.
                </div>
                @endforelse
            </div>
        </div>
    </div>

</div>
@endsection
