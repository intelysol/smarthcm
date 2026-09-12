@extends('workforce-admin.layout')

@section('title', 'Effective-Dated Changes & Impact Monitoring')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Effective-Dated & Backdated Changes</h1>
            <p class="text-sm text-slate-400 mt-1">Cross-domain workforce change tracking across upcoming time horizons and backdated risks.</p>
        </div>
    </div>

    <!-- Metric Horizon Cards -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="bg-slate-900 border border-slate-800 p-4 rounded-xl">
            <div class="text-xs text-slate-400 font-medium">Effective Today</div>
            <div class="text-2xl font-bold text-cyan-400 mt-1">{{ $changes['summary_counts']['today_count'] ?? 0 }}</div>
        </div>
        <div class="bg-slate-900 border border-slate-800 p-4 rounded-xl">
            <div class="text-xs text-slate-400 font-medium">Next 7 Days</div>
            <div class="text-2xl font-bold text-blue-400 mt-1">{{ $changes['summary_counts']['next_7_days_count'] ?? 0 }}</div>
        </div>
        <div class="bg-slate-900 border border-slate-800 p-4 rounded-xl">
            <div class="text-xs text-slate-400 font-medium">Next 30 Days</div>
            <div class="text-2xl font-bold text-indigo-400 mt-1">{{ $changes['summary_counts']['next_30_days_count'] ?? 0 }}</div>
        </div>
        <div class="bg-slate-900 border border-slate-800 p-4 rounded-xl">
            <div class="text-xs text-slate-400 font-medium">Next 90 Days</div>
            <div class="text-2xl font-bold text-purple-400 mt-1">{{ $changes['summary_counts']['next_90_days_count'] ?? 0 }}</div>
        </div>
        <div class="bg-slate-900 border border-rose-500/30 p-4 rounded-xl bg-rose-500/5">
            <div class="text-xs text-rose-300 font-medium">Backdated Changes</div>
            <div class="text-2xl font-bold text-rose-400 mt-1">{{ $changes['summary_counts']['backdated_count'] ?? 0 }}</div>
        </div>
    </div>

    <!-- Upcoming & Backdated Tables -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Upcoming Changes -->
        <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden shadow-xl">
            <div class="px-6 py-4 border-b border-slate-800 bg-slate-850 flex items-center justify-between">
                <h3 class="font-semibold text-white text-sm">Upcoming Effective Changes (7 - 30 Days)</h3>
                <span class="text-xs text-slate-400 font-mono">Future Pipeline</span>
            </div>
            <div class="divide-y divide-slate-800">
                @forelse($changes['next_7_days']->merge($changes['next_30_days'])->take(6) as $change)
                    <div class="px-6 py-4 flex items-center justify-between hover:bg-slate-850/50 transition">
                        <div>
                            <div class="font-medium text-white text-sm">
                                {{ $change->employee?->first_name }} {{ $change->employee?->last_name }}
                            </div>
                            <div class="text-xs text-slate-400 mt-0.5">
                                <span class="text-cyan-400 font-medium">{{ $change->actionType?->name ?? 'Personnel Action' }}</span> &middot; {{ $change->request_number }}
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="px-2 py-0.5 rounded text-xs font-mono font-medium bg-cyan-500/10 text-cyan-300 border border-cyan-500/20">
                                {{ \Carbon\Carbon::parse($change->effective_date)->format('M d, Y') }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-slate-500 text-xs">No upcoming changes detected.</div>
                @endforelse
            </div>
        </div>

        <!-- Backdated Changes -->
        <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden shadow-xl">
            <div class="px-6 py-4 border-b border-slate-800 bg-slate-850 flex items-center justify-between">
                <h3 class="font-semibold text-white text-sm flex items-center text-rose-400">
                    <i class="fa-solid fa-triangle-exclamation mr-2"></i> Backdated Changes
                </h3>
                <span class="text-xs text-rose-400 font-mono">High Risk Audit</span>
            </div>
            <div class="divide-y divide-slate-800">
                @forelse($changes['backdated']->take(6) as $change)
                    <div class="px-6 py-4 flex items-center justify-between hover:bg-slate-850/50 transition">
                        <div>
                            <div class="font-medium text-white text-sm">
                                {{ $change->employee?->first_name }} {{ $change->employee?->last_name }}
                            </div>
                            <div class="text-xs text-slate-400 mt-0.5">
                                {{ $change->actionType?->name ?? 'Action' }} &middot; Status: <span class="text-amber-400">{{ $change->status }}</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="px-2 py-0.5 rounded text-xs font-mono font-medium bg-rose-500/10 text-rose-300 border border-rose-500/20">
                                Eff: {{ \Carbon\Carbon::parse($change->effective_date)->format('M d, Y') }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-slate-500 text-xs">No backdated changes detected.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
