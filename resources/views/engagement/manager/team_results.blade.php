@extends('layouts.engagement')

@section('title', 'Team Survey Results - ' . $campaign->name)

@section('content')
<div class="space-y-8">
    <div class="flex justify-between items-start md:items-center bg-slate-800/80 p-6 rounded-2xl border border-slate-700/60 shadow-xl">
        <div>
            <a href="{{ route('engagement.manager.dashboard') }}" class="text-xs text-pink-400 hover:text-pink-300 font-semibold mb-2 inline-block">&larr; Back to Team Dashboard</a>
            <h1 class="text-2xl font-bold text-white">{{ $campaign->name }}</h1>
            <p class="text-xs text-slate-400 mt-1">Aggregated team survey insights and sentiment analytics.</p>
        </div>
        <span class="text-xs text-slate-400 bg-slate-900/60 px-3 py-1.5 rounded-lg border border-slate-700">
            Min Group: {{ $campaign->minimum_response_threshold }}
        </span>
    </div>

    @if(!empty($results['suppressed']))
    <div class="bg-slate-800/40 border border-slate-700/60 rounded-2xl p-12 text-center space-y-3 shadow-xl">
        <div class="w-16 h-16 bg-amber-500/10 text-amber-400 rounded-full flex items-center justify-center mx-auto text-2xl border border-amber-500/20">
            <i class="fa-solid fa-user-shield"></i>
        </div>
        <h2 class="text-base font-bold text-white">Results Suppressed to Protect Anonymity</h2>
        <p class="text-xs text-slate-400 max-w-md mx-auto">{{ $results['reason'] ?? 'Fewer than the minimum threshold of respondents participated in this demographic slice.' }}</p>
        <p class="text-[11px] text-slate-500">Flow HCM automatically suppresses team-level breakdowns when sample sizes are small to preserve full respondent privacy.</p>
    </div>
    @else
    <!-- KPIs -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-slate-800/60 border border-slate-700/60 rounded-2xl p-5 shadow-lg">
            <span class="text-xs text-slate-400 uppercase font-semibold">Total Responses</span>
            <div class="text-3xl font-extrabold text-white mt-1">{{ $results['total_responses'] ?? 0 }}</div>
            <span class="text-[11px] text-emerald-400 mt-1 block">Meets anonymity threshold</span>
        </div>

        <div class="bg-slate-800/60 border border-slate-700/60 rounded-2xl p-5 shadow-lg">
            <span class="text-xs text-slate-400 uppercase font-semibold">Overall Favorability</span>
            <div class="text-3xl font-extrabold text-pink-400 mt-1">{{ $results['overall_favorability'] ?? 0 }}%</div>
            <span class="text-[11px] text-slate-400 mt-1 block">Positive response ratio</span>
        </div>

        <div class="bg-slate-800/60 border border-slate-700/60 rounded-2xl p-5 shadow-lg">
            <span class="text-xs text-slate-400 uppercase font-semibold">eNPS Score</span>
            <div class="text-3xl font-extrabold text-indigo-400 mt-1">{{ $results['nps']['nps_score'] ?? '0' }}</div>
            <span class="text-[11px] text-slate-400 mt-1 block">{{ $results['nps']['promoter_percentage'] ?? 0 }}% Promoters vs {{ $results['nps']['detractor_percentage'] ?? 0 }}% Detractors</span>
        </div>
    </div>

    <!-- Dimension Breakdown -->
    <div class="bg-slate-800/50 border border-slate-700/60 rounded-2xl p-6 shadow-xl">
        <h2 class="text-base font-bold text-white mb-4">Dimension Scores</h2>
        <div class="space-y-4">
            @foreach($results['dimension_scores'] ?? [] as $dim)
            <div>
                <div class="flex justify-between text-xs font-semibold mb-1">
                    <span class="text-slate-200">{{ ucfirst(str_replace('_', ' ', $dim['dimension'])) }}</span>
                    <span class="text-pink-400">{{ $dim['favorability_rate'] }}% Favorable (Avg: {{ $dim['average_score'] }}/5)</span>
                </div>
                <div class="w-full bg-slate-900 rounded-full h-2 overflow-hidden border border-slate-700/50">
                    <div class="bg-gradient-to-r from-pink-500 to-rose-500 h-2 rounded-full" style="width: {{ $dim['favorability_rate'] }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
