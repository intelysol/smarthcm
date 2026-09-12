@extends('layouts.engagement')

@section('title', 'Executive Engagement Briefing')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 p-8 rounded-3xl border border-slate-700/60 shadow-2xl">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-pink-500/10 text-pink-400 border border-pink-500/20">
                    <i class="fa-solid fa-crown mr-1"></i> Executive Briefing
                </span>
            </div>
            <h1 class="text-3xl font-extrabold text-white">Enterprise Engagement & Culture Intelligence</h1>
            <p class="text-slate-400 text-sm mt-1">High-level strategic overview of organizational health, sentiment vectors, eNPS, and cultural alignment.</p>
        </div>
        <div class="text-right">
            <span class="text-xs text-slate-400 block">Organization Turnout</span>
            <span class="text-2xl font-black text-emerald-400">{{ $metrics['survey_response_rate'] ?? 0 }}%</span>
        </div>
    </div>

    <!-- Executive KPI Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-slate-800/70 border border-slate-700/60 rounded-3xl p-6 shadow-xl">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Enterprise eNPS</span>
            <div class="text-4xl font-black text-indigo-400 mt-2">{{ $metrics['enps'] ?? 0 }}</div>
            <p class="text-xs text-slate-400 mt-2">Net Promoter score based on employee willingness to recommend company as a great place to work.</p>
        </div>

        <div class="bg-slate-800/70 border border-slate-700/60 rounded-3xl p-6 shadow-xl">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Favorability Index</span>
            <div class="text-4xl font-black text-pink-400 mt-2">{{ $metrics['engagement_score'] ?? 0 }}%</div>
            <p class="text-xs text-slate-400 mt-2">Percentage of favorable ratings across all survey questions and continuous pulse checks.</p>
        </div>

        <div class="bg-slate-800/70 border border-slate-700/60 rounded-3xl p-6 shadow-xl">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Peer Recognition</span>
            <div class="text-4xl font-black text-amber-400 mt-2">{{ (int) ($metrics['recognition_count'] ?? 0) }}</div>
            <p class="text-xs text-slate-400 mt-2">Published employee-to-employee recognitions reinforcing organizational values.</p>
        </div>

        <div class="bg-slate-800/70 border border-slate-700/60 rounded-3xl p-6 shadow-xl">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Innovation Rate</span>
            <div class="text-4xl font-black text-emerald-400 mt-2">{{ $metrics['suggestion_implementation_rate'] ?? 0 }}%</div>
            <p class="text-xs text-slate-400 mt-2">Rate of bottom-up employee improvement ideas reviewed, accepted, and implemented.</p>
        </div>
    </div>

    <!-- Active Campaigns Overview -->
    <div class="bg-slate-800/50 border border-slate-700/60 rounded-3xl p-6 shadow-xl space-y-4">
        <h2 class="text-lg font-bold text-white">Active Strategic Campaigns</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse($campaigns as $camp)
            <div class="p-5 bg-slate-900/60 border border-slate-700/50 rounded-2xl flex justify-between items-center">
                <div>
                    <h3 class="text-sm font-bold text-white">{{ $camp->name }}</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Timeline: {{ $camp->start_date?->format('M d') }} to {{ $camp->end_date?->format('M d, Y') }}</p>
                </div>
                <a href="{{ route('engagement.admin.results', $camp->id) }}" class="px-3.5 py-2 bg-pink-500/10 hover:bg-pink-500/20 text-pink-400 border border-pink-500/30 text-xs font-bold rounded-xl transition">
                    View Analytics &rarr;
                </a>
            </div>
            @empty
            <p class="text-xs text-slate-400 col-span-full">No active campaigns running right now.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
