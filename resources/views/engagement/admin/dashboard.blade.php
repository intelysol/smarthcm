@extends('layouts.engagement')

@section('title', 'HR Engagement Administration')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-slate-800/80 p-6 rounded-2xl border border-slate-700/60 shadow-xl">
        <div>
            <h1 class="text-2xl font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-chart-pie text-pink-400"></i> Engagement & Culture Administration
            </h1>
            <p class="text-slate-400 text-sm mt-1">Manage survey campaigns, analyze organizational eNPS, review culture initiatives, and monitor action plans.</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('engagement.admin.surveys') }}" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white text-xs font-semibold rounded-xl transition">
                <i class="fa-solid fa-sliders mr-1.5"></i> Survey Bank
            </a>
            <a href="{{ route('engagement.admin.executive') }}" class="px-4 py-2 bg-gradient-to-r from-pink-500 to-rose-500 hover:from-pink-600 hover:to-rose-600 text-white text-xs font-bold rounded-xl shadow-lg shadow-pink-500/25 transition">
                <i class="fa-solid fa-crown mr-1.5"></i> Executive Briefing
            </a>
        </div>
    </div>

    <!-- Top KPI Metrics -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
        <div class="bg-slate-800/60 border border-slate-700/60 rounded-2xl p-5 shadow-lg">
            <span class="text-xs text-slate-400 uppercase font-semibold">Engagement Score</span>
            <div class="text-3xl font-extrabold text-pink-400 mt-1">{{ $metrics['engagement_score'] ?? 0 }}%</div>
            <span class="text-[11px] text-slate-400 mt-1 block">Positive favorability index</span>
        </div>

        <div class="bg-slate-800/60 border border-slate-700/60 rounded-2xl p-5 shadow-lg">
            <span class="text-xs text-slate-400 uppercase font-semibold">eNPS Index</span>
            <div class="text-3xl font-extrabold text-indigo-400 mt-1">{{ $metrics['enps'] ?? 0 }}</div>
            <span class="text-[11px] text-slate-400 mt-1 block">Employee net promoter score</span>
        </div>

        <div class="bg-slate-800/60 border border-slate-700/60 rounded-2xl p-5 shadow-lg">
            <span class="text-xs text-slate-400 uppercase font-semibold">Avg Participation</span>
            <div class="text-3xl font-extrabold text-emerald-400 mt-1">{{ $metrics['survey_response_rate'] ?? 0 }}%</div>
            <span class="text-[11px] text-slate-400 mt-1 block">Active recipient turnout</span>
        </div>

        <div class="bg-slate-800/60 border border-slate-700/60 rounded-2xl p-5 shadow-lg">
            <span class="text-xs text-slate-400 uppercase font-semibold">Action Plan Delivery</span>
            <div class="text-3xl font-extrabold text-amber-400 mt-1">{{ $metrics['action_plan_completion_rate'] ?? 0 }}%</div>
            <span class="text-[11px] text-slate-400 mt-1 block">Post-survey closure rate</span>
        </div>
    </div>

    <!-- Active & Recent Campaigns -->
    <div class="bg-slate-800/50 border border-slate-700/60 rounded-2xl p-6 shadow-xl space-y-4">
        <div class="flex justify-between items-center">
            <h2 class="text-base font-bold text-white">Campaign Management Roster</h2>
            <a href="{{ route('engagement.admin.surveys') }}" class="text-xs text-pink-400 hover:text-pink-300 font-semibold">+ New Campaign</a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-700/60 text-xs text-left">
                <thead class="bg-slate-900/50 text-slate-400 uppercase font-semibold">
                    <tr>
                        <th class="px-6 py-3.5">Campaign Name</th>
                        <th class="px-6 py-3.5">Survey</th>
                        <th class="px-6 py-3.5">Confidentiality</th>
                        <th class="px-6 py-3.5">Period</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/40 text-slate-200">
                    @forelse($campaigns as $camp)
                    <tr class="hover:bg-slate-800/50 transition">
                        <td class="px-6 py-4 font-semibold text-white">{{ $camp->name }}</td>
                        <td class="px-6 py-4">{{ $camp->survey?->title }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-0.5 rounded text-[11px] font-semibold {{ $camp->survey?->confidentiality_type === 'anonymous' ? 'bg-indigo-500/20 text-indigo-300' : 'bg-emerald-500/20 text-emerald-300' }}">
                                {{ ucfirst($camp->survey?->confidentiality_type ?? 'anonymous') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-slate-400">{{ $camp->start_date?->format('M d') }} - {{ $camp->end_date?->format('M d, Y') }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-0.5 rounded text-[11px] font-semibold {{ $camp->status === 'active' ? 'bg-emerald-500/20 text-emerald-300' : 'bg-slate-700 text-slate-300' }}">
                                {{ ucfirst($camp->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <a href="{{ route('engagement.admin.results', $camp->id) }}" class="text-pink-400 hover:text-pink-300 font-semibold mr-3">
                                <i class="fa-solid fa-chart-column mr-1"></i> Results
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-slate-400">No campaigns found. Create your first survey campaign!</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
