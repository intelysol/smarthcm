@extends('layouts.engagement')

@section('title', 'Campaign Results - ' . $campaign->name)

@section('content')
<div class="space-y-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-slate-800/80 p-6 rounded-2xl border border-slate-700/60 shadow-xl">
        <div>
            <a href="{{ route('engagement.admin.dashboard') }}" class="text-xs text-pink-400 hover:text-pink-300 font-semibold mb-2 inline-block">&larr; Back to Dashboard</a>
            <h1 class="text-2xl font-bold text-white">{{ $campaign->name }}</h1>
            <p class="text-xs text-slate-400 mt-1">Aggregated Campaign Analytics &bull; {{ $campaign->start_date?->format('M d') }} - {{ $campaign->end_date?->format('M d, Y') }}</p>
        </div>
        <div class="flex gap-3">
            <span class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                <i class="fa-solid fa-users mr-1.5"></i> {{ $results['total_responses'] ?? 0 }} Valid Responses
            </span>
        </div>
    </div>

    <!-- KPI Summary Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-slate-800/60 border border-slate-700/60 rounded-2xl p-6 shadow-lg">
            <span class="text-xs text-slate-400 uppercase font-semibold">eNPS Index</span>
            <div class="text-4xl font-extrabold text-indigo-400 mt-2">{{ $nps['nps_score'] ?? 0 }}</div>
            <div class="flex gap-2 text-[11px] mt-2 pt-2 border-t border-slate-700/50">
                <span class="text-emerald-400 font-bold">{{ $nps['promoter_percentage'] ?? 0 }}% Promoters</span>
                <span class="text-slate-400">&bull;</span>
                <span class="text-slate-300 font-bold">{{ $nps['passive_percentage'] ?? 0 }}% Passives</span>
                <span class="text-slate-400">&bull;</span>
                <span class="text-rose-400 font-bold">{{ $nps['detractor_percentage'] ?? 0 }}% Detractors</span>
            </div>
        </div>

        <div class="bg-slate-800/60 border border-slate-700/60 rounded-2xl p-6 shadow-lg">
            <span class="text-xs text-slate-400 uppercase font-semibold">Overall Favorability</span>
            <div class="text-4xl font-extrabold text-pink-400 mt-2">{{ $results['overall_favorability'] ?? 0 }}%</div>
            <span class="text-[11px] text-slate-400 mt-2 block">Positive response ratio across all rating items</span>
        </div>

        <div class="bg-slate-800/60 border border-slate-700/60 rounded-2xl p-6 shadow-lg">
            <span class="text-xs text-slate-400 uppercase font-semibold">Response Participation</span>
            <div class="text-4xl font-extrabold text-emerald-400 mt-2">{{ $results['response_rate']['response_rate'] ?? 0 }}%</div>
            <span class="text-[11px] text-slate-400 mt-2 block">{{ $results['response_rate']['completed_count'] ?? 0 }} completed of {{ $results['response_rate']['total_recipients'] ?? 0 }} invited</span>
        </div>
    </div>

    <!-- Dimension Performance -->
    <div class="bg-slate-800/50 border border-slate-700/60 rounded-2xl p-6 shadow-xl space-y-4">
        <h2 class="text-base font-bold text-white">Dimension Scores</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse($dimensions as $dim)
            <div class="p-4 bg-slate-900/60 border border-slate-700/50 rounded-xl space-y-2">
                <div class="flex justify-between text-xs font-semibold">
                    <span class="text-white">{{ ucfirst(str_replace('_', ' ', $dim['dimension'])) }}</span>
                    <span class="text-pink-400 font-bold">{{ $dim['favorability_rate'] }}% Favorable (Avg: {{ $dim['average_score'] }}/5)</span>
                </div>
                <div class="w-full bg-slate-800 rounded-full h-2.5 overflow-hidden">
                    <div class="bg-gradient-to-r from-pink-500 to-rose-500 h-2.5 rounded-full" style="width: {{ $dim['favorability_rate'] }}%"></div>
                </div>
            </div>
            @empty
            <p class="text-xs text-slate-400 col-span-full">No dimension scores computed yet.</p>
            @endforelse
        </div>
    </div>

    <!-- Question-by-Question Breakdown -->
    <div class="bg-slate-800/50 border border-slate-700/60 rounded-2xl p-6 shadow-xl space-y-4">
        <h2 class="text-base font-bold text-white">Question Breakdowns</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-700/60 text-xs text-left">
                <thead class="bg-slate-900/50 text-slate-400 uppercase font-semibold">
                    <tr>
                        <th class="px-6 py-3.5">Question</th>
                        <th class="px-6 py-3.5">Dimension</th>
                        <th class="px-6 py-3.5">Responses</th>
                        <th class="px-6 py-3.5">Average</th>
                        <th class="px-6 py-3.5">Favorability</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/40 text-slate-200">
                    @forelse($results['question_results'] ?? [] as $qRes)
                    <tr class="hover:bg-slate-800/50 transition">
                        <td class="px-6 py-4 font-semibold text-white max-w-md">{{ $qRes['question'] }}</td>
                        <td class="px-6 py-4">{{ ucfirst(str_replace('_', ' ', $qRes['dimension'])) }}</td>
                        <td class="px-6 py-4 text-slate-400">{{ $qRes['response_count'] }}</td>
                        <td class="px-6 py-4 font-bold text-white">{{ $qRes['average_score'] ?? 'N/A' }}</td>
                        <td class="px-6 py-4">
                            @if($qRes['favorability_rate'] !== null)
                            <span class="px-2 py-0.5 rounded font-bold bg-pink-500/10 text-pink-400 border border-pink-500/20">
                                {{ $qRes['favorability_rate'] }}%
                            </span>
                            @else
                            <span class="text-slate-500">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-slate-400">No question response breakdowns available.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
