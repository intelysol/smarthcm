@extends('shells.operations')

@section('title', 'Active Alerts & Rules — Operations')

@section('content')
<div class="space-y-6">

    <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-6 shadow-md flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-mono text-[#C9A227] font-semibold uppercase tracking-wider mb-1">
                <i class="fa-solid fa-bell text-[#C9A227]"></i>
                <span>Automated Alert Monitoring</span>
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight">Active Alerts &amp; Rules</h1>
            <p class="text-xs text-zinc-400 mt-0.5">Threshold evaluation rules, active breach triggers, and alert notification channels.</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('operations.dashboard') }}" class="px-3.5 py-2 rounded-xl bg-zinc-800 hover:bg-zinc-700 text-zinc-200 font-semibold text-xs transition border border-zinc-700 flex items-center">
                <i class="fa-solid fa-arrow-left mr-1.5"></i> Control Plane
            </a>
        </div>
    </div>

    <!-- Active Alerts Table -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-6 shadow-md">
        <h2 class="text-base font-bold text-white mb-4 flex items-center gap-2">
            <i class="fa-solid fa-triangle-exclamation text-rose-400"></i>
            Operational Alerts Log
        </h2>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-zinc-800 text-zinc-400 uppercase tracking-wider">
                        <th class="pb-3 font-semibold">Message</th>
                        <th class="pb-3 font-semibold">Severity</th>
                        <th class="pb-3 font-semibold">Status</th>
                        <th class="pb-3 font-semibold">Triggered Payload</th>
                        <th class="pb-3 font-semibold text-right">Triggered At</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/50">
                    @forelse($alerts as $alert)
                    <tr class="hover:bg-zinc-800/30 transition">
                        <td class="py-3 font-medium text-white">{{ $alert->message }}</td>
                        <td class="py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                {{ strtolower($alert->severity) === 'critical' ? 'bg-rose-500/10 text-rose-400 border border-rose-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20' }}">
                                {{ $alert->severity }}
                            </span>
                        </td>
                        <td class="py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                {{ $alert->status === 'open' ? 'bg-rose-500/10 text-rose-400' : 'bg-emerald-500/10 text-emerald-400' }}">
                                {{ $alert->status }}
                            </span>
                        </td>
                        <td class="py-3 text-zinc-300 font-mono text-[11px]">
                            {{ json_encode($alert->payload) }}
                        </td>
                        <td class="py-3 text-right text-zinc-400 font-mono text-[11px]">
                            {{ $alert->triggered_at?->format('Y-m-d H:i:s') ?? 'N/A' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-zinc-500 text-xs">
                            <i class="fa-solid fa-circle-check text-emerald-400 text-lg mb-1 block"></i>
                            No operational alerts currently triggered.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Alert Rules Grid -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-6 shadow-md">
        <h2 class="text-base font-bold text-white mb-4 flex items-center gap-2">
            <i class="fa-solid fa-sliders text-[#C9A227]"></i>
            Configured Threshold Rules
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($rules as $rule)
            <div class="p-4 rounded-xl border border-zinc-800 bg-zinc-950">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-bold text-white">{{ $rule->name }}</span>
                    <span class="text-[10px] font-bold uppercase px-1.5 py-0.5 rounded bg-zinc-800 text-zinc-300">{{ $rule->severity }}</span>
                </div>
                <p class="text-[11px] text-zinc-400 font-mono">
                    Condition: {{ $rule->metric }} {{ $rule->operator }} {{ $rule->threshold }}
                </p>
                <div class="mt-2 text-[10px] text-zinc-500">
                    Channels: {{ is_array($rule->channels) ? implode(', ', $rule->channels) : 'default' }}
                </div>
            </div>
            @empty
            <div class="col-span-3 text-center py-6 text-zinc-500 text-xs">
                Standard baseline rules loaded via alert-rules.yaml.
            </div>
            @endforelse
        </div>
    </div>

</div>
@endsection
