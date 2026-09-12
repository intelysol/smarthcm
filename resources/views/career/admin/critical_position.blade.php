@extends('layouts.career')

@section('title', 'Critical Position Roster')

@section('content')
<div class="space-y-6">
    <div class="bg-slate-800/80 p-6 rounded-2xl border border-slate-700 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <span class="text-xs font-bold uppercase tracking-wider text-emerald-400">Critical Position</span>
            <h1 class="text-2xl font-bold text-white mt-0.5">{{ $position->position?->title ?? $position->job?->title }}</h1>
            <p class="text-sm text-slate-400 mt-1">Current Incumbent: <strong>{{ $position->incumbent ? $position->incumbent->first_name . ' ' . $position->incumbent->last_name : 'Vacant' }}</strong></p>
        </div>
        <div class="flex items-center gap-3">
            <span class="px-3.5 py-1.5 rounded-xl text-xs font-bold bg-slate-900/80 text-white border border-slate-700">
                Risk Score: {{ $position->risk_score }} / 100
            </span>
        </div>
    </div>

    <!-- Successor Candidates -->
    <div class="bg-slate-800/60 p-6 rounded-2xl border border-slate-700/80 space-y-4">
        <h3 class="text-lg font-bold text-white flex items-center gap-2">
            <i class="fa-solid fa-users text-emerald-400"></i> Successor Candidates ({{ $position->candidates->count() }})
        </h3>

        <div class="space-y-4">
            @forelse($position->candidates as $c)
            <div class="bg-slate-900/60 p-4 rounded-xl border border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-emerald-500/20 text-emerald-300 font-bold flex items-center justify-center border border-emerald-500/30">
                        #{{ $c->priority }}
                    </div>
                    <div>
                        <h4 class="text-white font-bold text-sm">{{ $c->employee?->first_name }} {{ $c->employee?->last_name }}</h4>
                        <p class="text-xs text-slate-400">{{ $c->employee?->designation?->title ?? 'Team Member' }} &bull; Readiness: {{ ucwords(str_replace('_', ' ', $c->readiness_timeframe)) }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <span class="text-xs font-bold text-emerald-400 bg-slate-800 px-3 py-1.5 rounded-lg border border-slate-700">
                        Score: {{ $c->readiness_score }}%
                    </span>
                    @if($c->is_emergency_choice)
                    <span class="text-xs font-bold text-rose-300 bg-rose-500/20 px-2.5 py-1 rounded-md border border-rose-500/30">
                        Emergency Choice
                    </span>
                    @endif
                </div>
            </div>
            @empty
            <p class="text-xs text-slate-400">No candidates mapped to this position yet.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
