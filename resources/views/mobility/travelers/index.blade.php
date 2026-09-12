@extends('mobility.layout')

@section('title', 'Business Travelers')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Business Traveler Registry & Compliance</h1>
            <p class="text-sm text-slate-400">Track frequent international cross-border trips to prevent permanent establishment (PE) and tax residency breaches.</p>
        </div>
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="text-slate-400 bg-slate-850/60 uppercase tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="py-3 px-4">Trip #</th>
                        <th class="py-3 px-4">Employee</th>
                        <th class="py-3 px-4">Destination</th>
                        <th class="py-3 px-4">Trip Dates</th>
                        <th class="py-3 px-4">Duration</th>
                        <th class="py-3 px-4">PE Risk Level</th>
                        <th class="py-3 px-4">Visa Cleared</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($travelers as $t)
                    <tr class="hover:bg-slate-850/50">
                        <td class="py-3 px-4 font-mono text-cyan-300">{{ $t->traveler_trip_number }}</td>
                        <td class="py-3 px-4 font-semibold text-white">{{ $t->employee?->first_name }} {{ $t->employee?->last_name }}</td>
                        <td class="py-3 px-4 font-medium">{{ $t->destination_city ? $t->destination_city . ', ' : '' }}{{ $t->destination_country }}</td>
                        <td class="py-3 px-4">{{ $t->start_date?->format('Y-m-d') }} &rarr; {{ $t->end_date?->format('Y-m-d') }}</td>
                        <td class="py-3 px-4 font-mono">{{ $t->trip_days }} days</td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                                {{ $t->compliance_risk_level === 'high' ? 'bg-rose-500/10 text-rose-400' : ($t->compliance_risk_level === 'medium' ? 'bg-amber-500/10 text-amber-400' : 'bg-emerald-500/10 text-emerald-400') }}">
                                {{ strtoupper($t->compliance_risk_level) }}
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            @if($t->visa_cleared)
                                <span class="text-emerald-400"><i class="fa-solid fa-circle-check mr-1"></i> Cleared</span>
                            @else
                                <span class="text-amber-400"><i class="fa-solid fa-triangle-exclamation mr-1"></i> Action Required</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-500">No cross-border traveler records found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
