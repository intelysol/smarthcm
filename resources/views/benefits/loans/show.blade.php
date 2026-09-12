@extends('benefits.layout')

@section('title', "Loan {$loan->application_number}")

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('benefits.loans.index') }}" class="text-slate-400 hover:text-white text-sm">&larr; Back to Loans</a>
                <span class="text-slate-600">|</span>
                <span class="font-mono text-emerald-400 font-bold">{{ $loan->application_number }}</span>
            </div>
            <h1 class="text-2xl font-bold text-white mt-1">{{ $loan->product?->name }} &bull; {{ $loan->employee?->first_name }} {{ $loan->employee?->last_name }}</h1>
        </div>
        <div>
            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-emerald-900/60 text-emerald-300 border border-emerald-700/50 uppercase tracking-wider">
                {{ ucfirst($loan->status) }}
            </span>
        </div>
    </div>

    <!-- Loan Overview Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
            <span class="text-xs text-slate-400 uppercase">Principal Disbursed</span>
            <div class="text-xl font-bold text-white mt-1">${{ number_format((float)$loan->approved_amount, 2) }}</div>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
            <span class="text-xs text-slate-400 uppercase">Annual Interest Rate</span>
            <div class="text-xl font-bold text-slate-200 mt-1">{{ number_format((float)$loan->interest_rate, 2) }}% ({{ ucwords(str_replace('_', ' ', $loan->interest_method)) }})</div>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
            <span class="text-xs text-slate-400 uppercase">Total Recovered</span>
            <div class="text-xl font-bold text-emerald-400 mt-1">${{ number_format((float)($loan->activeSchedule?->total_paid ?? 0), 2) }}</div>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
            <span class="text-xs text-slate-400 uppercase">Remaining Outstanding</span>
            <div class="text-xl font-bold text-amber-400 mt-1">${{ number_format((float)($loan->activeSchedule?->remaining_balance ?? 0), 2) }}</div>
        </div>
    </div>

    <!-- Amortization Schedule -->
    <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden shadow-sm">
        <div class="px-6 py-4 border-b border-slate-800 bg-slate-850 flex items-center justify-between">
            <h2 class="text-base font-bold text-white">Amortization Repayment Schedule (Version {{ $loan->activeSchedule?->schedule_version ?? 1 }})</h2>
            <span class="text-xs text-slate-400">Total Payable: ${{ number_format((float)($loan->activeSchedule?->total_payable ?? 0), 2) }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-slate-900 text-slate-400 text-xs uppercase tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="px-6 py-3">#</th>
                        <th class="px-6 py-3">Due Date</th>
                        <th class="px-6 py-3">Principal</th>
                        <th class="px-6 py-3">Interest</th>
                        <th class="px-6 py-3">Total Installment</th>
                        <th class="px-6 py-3">Paid Amount</th>
                        <th class="px-6 py-3">Balance</th>
                        <th class="px-6 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800 font-mono text-xs">
                    @if($loan->activeSchedule)
                        @foreach($loan->activeSchedule->installments as $inst)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="px-6 py-3 font-bold text-slate-400">{{ $inst->installment_number }}</td>
                            <td class="px-6 py-3 text-slate-200">{{ $inst->due_date?->format('Y-m-d') }}</td>
                            <td class="px-6 py-3 text-slate-300">${{ number_format((float)$inst->principal_amount, 2) }}</td>
                            <td class="px-6 py-3 text-slate-400">${{ number_format((float)$inst->interest_amount, 2) }}</td>
                            <td class="px-6 py-3 text-white font-bold">${{ number_format((float)$inst->total_installment, 2) }}</td>
                            <td class="px-6 py-3 text-emerald-400">${{ number_format((float)$inst->paid_amount, 2) }}</td>
                            <td class="px-6 py-3 text-slate-300">${{ number_format((float)$inst->balance_remaining, 2) }}</td>
                            <td class="px-6 py-3 font-sans">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $inst->status === 'deducted_payroll' ? 'bg-emerald-900/50 text-emerald-300' : 'bg-slate-800 text-slate-400' }}">
                                    {{ ucwords(str_replace('_', ' ', $inst->status)) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-slate-500 font-sans">No active repayment schedule generated.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
