@extends('mobility.layout')

@section('title', 'Mobility Requests')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Mobility Requests & Intake</h1>
            <p class="text-sm text-slate-400">Initiate international assignments, evaluate candidate eligibility, and route for approvals.</p>
        </div>
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="text-slate-400 bg-slate-850/60 uppercase tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="py-3 px-4">Request #</th>
                        <th class="py-3 px-4">Employee</th>
                        <th class="py-3 px-4">Home Country</th>
                        <th class="py-3 px-4">Host Country</th>
                        <th class="py-3 px-4">Start Date</th>
                        <th class="py-3 px-4">Eligibility</th>
                        <th class="py-3 px-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($requests as $r)
                    <tr class="hover:bg-slate-850/50">
                        <td class="py-3 px-4 font-mono text-indigo-300">{{ $r->request_number }}</td>
                        <td class="py-3 px-4 font-semibold text-white">{{ $r->employee?->first_name }} {{ $r->employee?->last_name }}</td>
                        <td class="py-3 px-4">{{ $r->home_country }}</td>
                        <td class="py-3 px-4 font-medium text-emerald-300">{{ $r->host_country }}</td>
                        <td class="py-3 px-4">{{ $r->proposed_start_date?->format('Y-m-d') }}</td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                                {{ $r->eligibility_status->value === 'eligible' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-amber-500/10 text-amber-400' }}">
                                {{ ucfirst(str_replace('_', ' ', $r->eligibility_status->value)) }}
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-800 text-slate-300">
                                {{ ucfirst($r->status->value) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-500">No requests submitted.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
