@extends('expenses.layout')

@section('title', "Claim {$claim->claim_number}")

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('expenses.claims.index') }}" class="text-slate-400 hover:text-white text-sm">&larr; Back to Claims</a>
                <span class="text-slate-600">|</span>
                <span class="font-mono text-indigo-400 font-bold">{{ $claim->claim_number }}</span>
            </div>
            <h1 class="text-2xl font-bold text-white mt-1">{{ $claim->title }}</h1>
            <div class="text-xs text-slate-400 mt-1">Submitted by {{ $claim->employee?->first_name }} {{ $claim->employee?->last_name }} on {{ $claim->claim_date?->format('Y-m-d') }}</div>
        </div>
        <div>
            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-indigo-900/60 text-indigo-300 border border-indigo-700/50 uppercase tracking-wider">
                {{ ucfirst(str_replace('_', ' ', $claim->status)) }}
            </span>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
            <span class="text-xs text-slate-400 uppercase">Gross Claimed</span>
            <div class="text-xl font-bold text-white mt-1">${{ number_format((float)$claim->claimed_total, 2) }}</div>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
            <span class="text-xs text-slate-400 uppercase">Policy Approved</span>
            <div class="text-xl font-bold text-emerald-400 mt-1">${{ number_format((float)$claim->approved_total, 2) }}</div>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
            <span class="text-xs text-slate-400 uppercase">Advance Offset</span>
            <div class="text-xl font-bold text-amber-400 mt-1">${{ number_format((float)$claim->advance_settled_amount, 2) }}</div>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
            <span class="text-xs text-slate-400 uppercase">Net Reimbursement</span>
            <div class="text-xl font-bold text-indigo-400 mt-1">${{ number_format((float)$claim->net_reimbursement_amount, 2) }}</div>
        </div>
    </div>

    <!-- Expense Lines Table -->
    <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden shadow-sm">
        <div class="px-6 py-4 border-b border-slate-800 bg-slate-850 flex items-center justify-between">
            <h2 class="text-base font-bold text-white">Itemized Expense Lines</h2>
            <span class="text-xs text-slate-400">{{ $claim->lines->count() }} line items</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-slate-900 text-slate-400 text-xs uppercase tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="px-6 py-3">Date</th>
                        <th class="px-6 py-3">Category</th>
                        <th class="px-6 py-3">Merchant &amp; Description</th>
                        <th class="px-6 py-3">Original Amount</th>
                        <th class="px-6 py-3">Base Amount</th>
                        <th class="px-6 py-3">Policy Status</th>
                        <th class="px-6 py-3 text-right">Approved Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800 font-mono text-xs">
                    @forelse($claim->lines as $line)
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="px-6 py-3 text-slate-300">{{ $line->expense_date?->format('Y-m-d') }}</td>
                        <td class="px-6 py-3 font-sans text-slate-200">{{ $line->category?->name }}</td>
                        <td class="px-6 py-3 font-sans">
                            <div class="font-medium text-white">{{ $line->merchant ?? 'N/A' }}</div>
                            <div class="text-xs text-slate-400">{{ $line->description }}</div>
                        </td>
                        <td class="px-6 py-3 text-slate-300">{{ $line->original_currency }} {{ number_format((float)$line->original_amount, 2) }}</td>
                        <td class="px-6 py-3 text-white font-bold">${{ number_format((float)$line->base_amount, 2) }}</td>
                        <td class="px-6 py-3 font-sans">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $line->policy_status === 'compliant' ? 'bg-emerald-900/50 text-emerald-300' : 'bg-amber-900/50 text-amber-300' }}">
                                {{ ucfirst(str_replace('_', ' ', $line->policy_status)) }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-right text-emerald-400 font-bold font-mono">
                            ${{ number_format((float)$line->approved_base_amount, 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-slate-500 font-sans">No expense lines attached to this claim.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
