@extends('layouts.career')

@section('title', 'Executive Talent Dashboard')

@section('content')
<div class="space-y-6">
    <div class="bg-slate-800/80 p-6 rounded-2xl border border-slate-700">
        <h1 class="text-2xl font-bold text-white flex items-center gap-2">
            <i class="fa-solid fa-chess-king text-emerald-400"></i> Executive Talent & Succession Overview
        </h1>
        <p class="text-sm text-slate-400 mt-1">Real-time workforce capability index, succession coverage, and high-potential pipelines.</p>
    </div>

    <!-- KPI Metric Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
        <div class="bg-slate-800/60 p-5 rounded-2xl border border-slate-700/80">
            <span class="text-xs text-slate-400 uppercase font-bold tracking-wider">Skill Coverage</span>
            <div class="text-2xl font-bold text-emerald-400 mt-1">{{ $stats['skill_coverage_rate'] ?? 100 }}%</div>
            <p class="text-xs text-slate-500 mt-1">Role requirements met</p>
        </div>
        <div class="bg-slate-800/60 p-5 rounded-2xl border border-slate-700/80">
            <span class="text-xs text-slate-400 uppercase font-bold tracking-wider">Succession Coverage</span>
            <div class="text-2xl font-bold text-teal-400 mt-1">{{ $stats['succession_coverage'] ?? 100 }}%</div>
            <p class="text-xs text-slate-500 mt-1">Critical positions backed</p>
        </div>
        <div class="bg-slate-800/60 p-5 rounded-2xl border border-slate-700/80">
            <span class="text-xs text-slate-400 uppercase font-bold tracking-wider">Ready Now Rate</span>
            <div class="text-2xl font-bold text-emerald-300 mt-1">{{ $stats['ready_now_successor_rate'] ?? 0 }}%</div>
            <p class="text-xs text-slate-500 mt-1">Immediate successors</p>
        </div>
        <div class="bg-slate-800/60 p-5 rounded-2xl border border-slate-700/80">
            <span class="text-xs text-slate-400 uppercase font-bold tracking-wider">High Potential</span>
            <div class="text-2xl font-bold text-indigo-400 mt-1">{{ $stats['high_potential_count'] ?? 0 }}</div>
            <p class="text-xs text-slate-500 mt-1">Identified future leaders</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Talent Pools -->
        <div class="bg-slate-800/60 p-6 rounded-2xl border border-slate-700/80 space-y-4">
            <h3 class="text-lg font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-users-viewfinder text-emerald-400"></i> Active Talent Pools
            </h3>
            <div class="space-y-3">
                @forelse($pools as $pool)
                <div class="p-3.5 bg-slate-900/60 rounded-xl border border-slate-800 flex justify-between items-center text-sm">
                    <div>
                        <span class="text-white font-bold block">{{ $pool->name }}</span>
                        <span class="text-xs text-slate-400">{{ $pool->code }}</span>
                    </div>
                    <span class="text-xs bg-emerald-500/20 text-emerald-300 px-2.5 py-1 rounded-md font-semibold border border-emerald-500/30">
                        {{ $pool->members_count }} Members
                    </span>
                </div>
                @empty
                <p class="text-xs text-slate-400">No active talent pools.</p>
                @endforelse
            </div>
        </div>

        <!-- Succession Plans -->
        <div class="bg-slate-800/60 p-6 rounded-2xl border border-slate-700/80 space-y-4">
            <h3 class="text-lg font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-shield-halved text-teal-400"></i> Succession Framework
            </h3>
            <div class="space-y-3">
                @forelse($plans as $plan)
                <div class="p-3.5 bg-slate-900/60 rounded-xl border border-slate-800 flex justify-between items-center text-sm">
                    <div>
                        <span class="text-white font-bold block">{{ $plan->name }}</span>
                        <span class="text-xs text-slate-400">Status: {{ ucwords($plan->status) }}</span>
                    </div>
                    <span class="text-xs bg-teal-500/20 text-teal-300 px-2.5 py-1 rounded-md font-semibold border border-teal-500/30">
                        {{ $plan->positions_count }} Critical Roles
                    </span>
                </div>
                @empty
                <p class="text-xs text-slate-400">No active succession plans.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
