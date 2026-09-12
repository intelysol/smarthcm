@extends('benefits.layout')

@section('title', 'Employee Loans & Advances')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Employee Loans & Advances Portfolio</h1>
            <p class="text-sm text-slate-400">Track loan applications, amortization schedules, disbursements, and recovery status.</p>
        </div>
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-slate-850 text-slate-400 text-xs uppercase tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="px-6 py-4">Application #</th>
                        <th class="px-6 py-4">Employee</th>
                        <th class="px-6 py-4">Loan Product</th>
                        <th class="px-6 py-4">Approved Amount</th>
                        <th class="px-6 py-4">Tenure &amp; Rate</th>
                        <th class="px-6 py-4">Remaining Balance</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($loans as $loan)
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="px-6 py-4 font-mono font-medium text-emerald-400">
                            {{ $loan->application_number }}
                        </td>
                        <td class="px-6 py-4 font-medium text-white">
                            <div>{{ $loan->employee?->first_name }} {{ $loan->employee?->last_name }}</div>
                            <div class="text-xs text-slate-500 font-mono">{{ $loan->employee?->employee_number }}</div>
                        </td>
                        <td class="px-6 py-4 text-slate-300">{{ $loan->product?->name }}</td>
                        <td class="px-6 py-4 text-slate-200 font-bold">${{ number_format((float)($loan->approved_amount ?? $loan->requested_amount), 2) }}</td>
                        <td class="px-6 py-4 text-xs text-slate-400">
                            {{ $loan->approved_tenure_months ?? $loan->requested_tenure_months }} mos @ {{ number_format((float)$loan->interest_rate, 2) }}%
                        </td>
                        <td class="px-6 py-4 text-amber-400 font-medium">
                            ${{ number_format((float)($loan->activeSchedule?->remaining_balance ?? 0), 2) }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-900/50 text-emerald-300 border border-emerald-700/50">
                                {{ ucfirst($loan->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('benefits.loans.show', $loan) }}" class="px-3 py-1.5 rounded-lg text-xs font-medium bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition">
                                View Schedule &rarr;
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-slate-500">No loan records found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($loans, 'links'))
        <div class="px-6 py-4 border-t border-slate-800 bg-slate-900/60">
            {{ $loans->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
