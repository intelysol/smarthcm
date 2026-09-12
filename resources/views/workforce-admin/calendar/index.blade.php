@extends('workforce-admin.layout')

@section('title', 'HR Operational Calendar')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">HR Operational Calendar & Deadlines</h1>
            <p class="text-sm text-slate-400">Consolidated timeline of joiners, leavers, probation completions, payroll cutoffs, and compliance expiries.</p>
        </div>
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="text-slate-400 bg-slate-850/60 uppercase tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="py-3 px-4">Event Date</th>
                        <th class="py-3 px-4">Event Type</th>
                        <th class="py-3 px-4">Title</th>
                        <th class="py-3 px-4">Domain</th>
                        <th class="py-3 px-4">Employee</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($events as $ev)
                    <tr class="hover:bg-slate-850/50">
                        <td class="py-3 px-4 font-mono text-indigo-300">{{ $ev->event_date?->format('Y-m-d') }}</td>
                        <td class="py-3 px-4">{{ ucwords(str_replace('_', ' ', $ev->event_type)) }}</td>
                        <td class="py-3 px-4 font-semibold text-white">{{ $ev->title }}</td>
                        <td class="py-3 px-4">{{ ucfirst($ev->source_domain) }}</td>
                        <td class="py-3 px-4">{{ $ev->employee ? $ev->employee->first_name . ' ' . $ev->employee->last_name : 'N/A' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-slate-500">No calendar events synced.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
