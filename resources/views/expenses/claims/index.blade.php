@extends('expenses.layout')

@section('title', 'Expense Claims')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Employee Expense Claims</h1>
            <p class="text-sm text-slate-400">Review employee business claims, multi-level approval queues, advance offsets, and net reimbursements.</p>
        </div>
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-slate-850 text-slate-400 text-xs uppercase tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="px-6 py-4">Claim # &amp; Title</th>
                        <th class="px-6 py-4">Employee</th>
                        <th class="px-6 py-4">Claim Date</th>
                        <th class="px-6 py-4">Claimed Total</th>
                        <th class="px-6 py-4">Approved Total</th>
                        <th class="px-6 py-4">Net Payable</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($claims as $claim)
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="px-6 py-4 font-medium text-white">
                            <div>{{ $claim->title }}</div>
                            <div class="text-xs text-indigo-400 font-mono">{{ $claim->claim_number }}</div>
                        </td>
                        <td class="px-6 py-4 font-medium text-white">
                            <div>{{ $claim->employee?->first_name }} {{ $claim->employee?->last_name }}</div>
                            <div class="text-xs text-slate-500 font-mono">{{ $claim->employee?->employee_number }}</div>
                        </td>
                        <td class="px-6 py-4 text-xs font-mono text-slate-400">{{ $claim->claim_date?->format('Y-m-d') }}</td>
                        <td class="px-6 py-4 text-slate-200">${{ number_format((float)$claim->claimed_total, 2) }}</td>
                        <td class="px-6 py-4 text-emerald-400 font-medium">${{ number_format((float)$claim->approved_total, 2) }}</td>
                        <td class="px-6 py-4 text-white font-bold">${{ number_format((float)$claim->net_reimbursement_amount, 2) }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-900/50 text-indigo-300 border border-indigo-700/50">
                                {{ ucfirst(str_replace('_', ' ', $claim->status)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('expenses.claims.show', $claim) }}" class="px-3 py-1.5 rounded-lg text-xs font-medium bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition">
                                View Details &rarr;
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-slate-500">No expense claims found.</td>
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
