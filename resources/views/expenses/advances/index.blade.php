@extends('expenses.layout')

@section('title', 'Travel Advances & Settlement')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Travel Advances &amp; Settlement Ledger</h1>
            <p class="text-sm text-slate-400">Manage employee cash/bank travel advances, disbursement logs, and claim offsets.</p>
        </div>
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-slate-850 text-slate-400 text-xs uppercase tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="px-6 py-4">Advance #</th>
                        <th class="px-6 py-4">Employee</th>
                        <th class="px-6 py-4">Requested</th>
                        <th class="px-6 py-4">Disbursed</th>
                        <th class="px-6 py-4">Settled</th>
                        <th class="px-6 py-4">Remaining Balance</th>
                        <th class="px-6 py-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($advances as $adv)
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="px-6 py-4 font-mono font-medium text-indigo-400">
                            {{ $adv->advance_number }}
                        </td>
                        <td class="px-6 py-4 font-medium text-white">
                            <div>{{ $adv->employee?->first_name }} {{ $adv->employee?->last_name }}</div>
                            <div class="text-xs text-slate-500 font-mono">{{ $adv->employee?->employee_number }}</div>
                        </td>
                        <td class="px-6 py-4 text-slate-300">${{ number_format((float)$adv->requested_amount, 2) }}</td>
                        <td class="px-6 py-4 text-emerald-400 font-medium">${{ number_format((float)$adv->disbursed_amount, 2) }}</td>
                        <td class="px-6 py-4 text-slate-400">${{ number_format((float)$adv->settled_amount, 2) }}</td>
                        <td class="px-6 py-4 font-bold text-amber-400">
                            ${{ number_format($adv->remainingUnsettledAmount(), 2) }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-900/50 text-indigo-300 border border-indigo-700/50">
                                {{ ucfirst(str_replace('_', ' ', $adv->status)) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-slate-500">No travel advances found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($advances, 'links'))
        <div class="px-6 py-4 border-t border-slate-800 bg-slate-900/60">
            {{ $advances->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
