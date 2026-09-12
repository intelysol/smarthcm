@extends('benefits.layout')

@section('title', 'Insurance & Medical Claims')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Insurance & Medical Claims</h1>
            <p class="text-sm text-slate-400">Review inpatient, outpatient, and medical claims with strict medical privacy protection.</p>
        </div>
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-slate-850 text-slate-400 text-xs uppercase tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="px-6 py-4">Claim #</th>
                        <th class="px-6 py-4">Employee</th>
                        <th class="px-6 py-4">Claim Type</th>
                        <th class="px-6 py-4">Incident Date</th>
                        <th class="px-6 py-4">Claimed Amount</th>
                        <th class="px-6 py-4">Approved Amount</th>
                        <th class="px-6 py-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($claims as $claim)
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="px-6 py-4 font-mono font-medium text-emerald-400">
                            {{ $claim->claim_number }}
                        </td>
                        <td class="px-6 py-4 font-medium text-white">
                            <div>{{ $claim->employee?->first_name }} {{ $claim->employee?->last_name }}</div>
                            <div class="text-xs text-slate-500 font-mono">{{ $claim->employee?->employee_number }}</div>
                        </td>
                        <td class="px-6 py-4 text-slate-300">{{ ucfirst($claim->claim_type) }}</td>
                        <td class="px-6 py-4 text-xs text-slate-400 font-mono">{{ $claim->incident_date?->format('Y-m-d') }}</td>
                        <td class="px-6 py-4 text-slate-200">${{ number_format((float)$claim->claimed_amount, 2) }}</td>
                        <td class="px-6 py-4 text-emerald-400 font-medium">${{ number_format((float)$claim->approved_amount, 2) }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-900/50 text-emerald-300 border border-emerald-700/50">
                                {{ ucfirst($claim->status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-slate-500">No claims submitted.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($claims, 'links'))
        <div class="px-6 py-4 border-t border-slate-800 bg-slate-900/60">
            {{ $claims->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
