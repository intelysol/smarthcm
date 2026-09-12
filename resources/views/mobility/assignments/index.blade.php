@extends('mobility.layout')

@section('title', 'Active Assignments')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Mobility Assignments Directory</h1>
            <p class="text-sm text-slate-400">Active expatriates, secondments, long-term transfers, and assignment cost profiles.</p>
        </div>
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="text-slate-400 bg-slate-850/60 uppercase tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="py-3 px-4">Assignment #</th>
                        <th class="py-3 px-4">Employee</th>
                        <th class="py-3 px-4">Route</th>
                        <th class="py-3 px-4">Dates</th>
                        <th class="py-3 px-4">Version</th>
                        <th class="py-3 px-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($assignments as $a)
                    <tr class="hover:bg-slate-850/50">
                        <td class="py-3 px-4 font-mono text-indigo-300">{{ $a->assignment_number }}</td>
                        <td class="py-3 px-4 font-semibold text-white">{{ $a->employee?->first_name }} {{ $a->employee?->last_name }}</td>
                        <td class="py-3 px-4 font-medium">{{ $a->home_country }} &rarr; {{ $a->host_country }}</td>
                        <td class="py-3 px-4">{{ $a->start_date?->format('Y-m-d') }} to {{ $a->planned_end_date?->format('Y-m-d') }}</td>
                        <td class="py-3 px-4 font-mono">v{{ $a->current_version }}</td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                                {{ $a->status->value === 'active' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-slate-700 text-slate-300' }}">
                                {{ ucfirst(str_replace('_', ' ', $a->status->value)) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-slate-500">No assignments active.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
