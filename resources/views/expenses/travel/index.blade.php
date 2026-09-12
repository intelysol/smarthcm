@extends('expenses.layout')

@section('title', 'Business Travel Requests')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Business Travel Requests &amp; Authorizations</h1>
            <p class="text-sm text-slate-400">Track domestic and international travel requests, multi-city itineraries, and budget authorizations.</p>
        </div>
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-slate-850 text-slate-400 text-xs uppercase tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="px-6 py-4">Request #</th>
                        <th class="px-6 py-4">Employee</th>
                        <th class="px-6 py-4">Destination &amp; Purpose</th>
                        <th class="px-6 py-4">Travel Dates</th>
                        <th class="px-6 py-4">Estimated Budget</th>
                        <th class="px-6 py-4">Authorization</th>
                        <th class="px-6 py-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($requests as $tr)
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="px-6 py-4 font-mono font-medium text-indigo-400">
                            {{ $tr->request_number }}
                        </td>
                        <td class="px-6 py-4 font-medium text-white">
                            <div>{{ $tr->employee?->first_name }} {{ $tr->employee?->last_name }}</div>
                            <div class="text-xs text-slate-500 font-mono">{{ $tr->employee?->employee_number }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-slate-200">{{ $tr->destination }}</div>
                            <div class="text-xs text-slate-400 truncate max-w-xs">{{ $tr->purpose }}</div>
                        </td>
                        <td class="px-6 py-4 text-xs font-mono text-slate-300">
                            {{ $tr->start_date?->format('Y-m-d') }} &rarr; {{ $tr->end_date?->format('Y-m-d') }}
                        </td>
                        <td class="px-6 py-4 text-white font-bold">${{ number_format((float)$tr->estimated_cost, 2) }}</td>
                        <td class="px-6 py-4">
                            @if($tr->authorization)
                                <span class="px-2 py-0.5 rounded text-xs font-mono bg-emerald-900/40 text-emerald-300 border border-emerald-700/40">
                                    {{ $tr->authorization->authorization_number }}
                                </span>
                            @else
                                <span class="text-xs text-slate-500 italic">None</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-900/50 text-indigo-300 border border-indigo-700/50">
                                {{ ucfirst($tr->status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-slate-500">No travel requests found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($requests, 'links'))
        <div class="px-6 py-4 border-t border-slate-800 bg-slate-900/60">
            {{ $requests->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
