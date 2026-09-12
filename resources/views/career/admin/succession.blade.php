@extends('layouts.career')

@section('title', 'Succession Planning & Critical Roles')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-800/80 p-6 rounded-2xl border border-slate-700">
        <div>
            <h1 class="text-2xl font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-sitemap text-emerald-400"></i> Succession Planning & Critical Roles
            </h1>
            <p class="text-sm text-slate-400 mt-1">Monitor critical leadership depth, emergency successors, and risk coverage.</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="px-3.5 py-1.5 rounded-xl text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                Coverage: {{ $coverage['coverage_rate'] }}%
            </span>
        </div>
    </div>

    <!-- Critical Positions Roster -->
    <div class="bg-slate-800/60 rounded-2xl border border-slate-700/80 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-slate-900/80 text-xs uppercase font-semibold text-slate-400 border-b border-slate-700">
                    <tr>
                        <th class="px-6 py-4">Critical Position</th>
                        <th class="px-6 py-4">Current Incumbent</th>
                        <th class="px-6 py-4">Criticality</th>
                        <th class="px-6 py-4">Successors</th>
                        <th class="px-6 py-4">Risk Index</th>
                        <th class="px-6 py-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/60">
                    @forelse($positions as $pos)
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="px-6 py-4 font-bold text-white">{{ $pos->position?->title ?? $pos->job?->title }}</td>
                        <td class="px-6 py-4 text-slate-300">{{ $pos->incumbent ? $pos->incumbent->first_name . ' ' . $pos->incumbent->last_name : 'Vacant' }}</td>
                        <td class="px-6 py-4">
                            <span class="text-xs uppercase font-bold tracking-wider px-2 py-0.5 rounded bg-slate-700 text-slate-300">
                                {{ str_replace('_', ' ', $pos->criticality) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-xs font-bold text-emerald-400 bg-emerald-500/10 px-2.5 py-1 rounded-md border border-emerald-500/20">
                                {{ $pos->candidates->count() }} Candidates
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-xs font-bold {{ $pos->risk_score >= 60 ? 'text-rose-400' : 'text-emerald-400' }}">
                                {{ $pos->risk_score }} / 100
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('career.admin.position_detail', $pos->id) }}" class="text-xs text-emerald-400 font-semibold hover:underline">Manage &rarr;</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-slate-400">No critical positions designated yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
