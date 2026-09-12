@extends('payroll.layout')

@section('title', "Payslip #{$payslip->payslip_number}")
@section('page_title', 'Employee Payslip View')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-slate-950 border border-slate-800 rounded-2xl p-8 shadow-xl">
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-slate-800 pb-6 mb-6">
            <div>
                <h3 class="text-2xl font-bold text-white tracking-tight">SmartHCM Enterprise</h3>
                <p class="text-xs text-slate-400">Official Earnings & Deduction Statement</p>
            </div>
            <div class="text-right">
                <span class="font-mono text-sm font-semibold text-emerald-400">{{ $payslip->payslip_number }}</span>
                <p class="text-xs text-slate-400 mt-1">Payment Date: {{ $payslip->pay_date->format('F d, Y') }}</p>
            </div>
        </div>

        <!-- Employee Info Details -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 bg-slate-900/50 rounded-xl p-4 border border-slate-800 mb-6 text-xs">
            <div>
                <span class="text-slate-500 uppercase tracking-wider text-[10px]">Employee Name</span>
                <p class="font-semibold text-white mt-0.5">{{ $payslip->employee?->fullName() }}</p>
            </div>
            <div>
                <span class="text-slate-500 uppercase tracking-wider text-[10px]">Employee ID</span>
                <p class="font-mono text-white mt-0.5">{{ $payslip->employee?->employee_number ?? $payslip->employee?->employee_code }}</p>
            </div>
            <div>
                <span class="text-slate-500 uppercase tracking-wider text-[10px]">Pay Period</span>
                <p class="text-white mt-0.5">{{ $payslip->period_start->format('M d') }} - {{ $payslip->period_end->format('M d, Y') }}</p>
            </div>
            <div>
                <span class="text-slate-500 uppercase tracking-wider text-[10px]">Disbursement Account</span>
                <p class="font-mono text-white mt-0.5">{{ $payslip->masked_bank_account }}</p>
            </div>
        </div>

        <!-- Itemized Earnings & Deductions Tables -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
            <!-- Earnings -->
            <div>
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Earnings</h4>
                <div class="border border-slate-800 rounded-xl divide-y divide-slate-800 overflow-hidden text-xs">
                    @if($payslip->snapshot && isset($payslip->snapshot->earnings_snapshot))
                        @foreach($payslip->snapshot->earnings_snapshot as $e)
                        <div class="p-3 flex items-center justify-between">
                            <span class="text-slate-300 font-medium">{{ $e['earning_name'] }}</span>
                            <span class="font-mono text-slate-200">${{ number_format($e['amount'], 2) }}</span>
                        </div>
                        @endforeach
                    @endif
                    <div class="p-3 bg-slate-900 flex items-center justify-between font-bold">
                        <span class="text-white">Total Gross Pay</span>
                        <span class="font-mono text-white">${{ number_format($payslip->gross_pay, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Deductions & Taxes -->
            <div>
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Deductions & Taxes</h4>
                <div class="border border-slate-800 rounded-xl divide-y divide-slate-800 overflow-hidden text-xs">
                    @if($payslip->snapshot && isset($payslip->snapshot->deductions_snapshot))
                        @foreach($payslip->snapshot->deductions_snapshot as $d)
                        <div class="p-3 flex items-center justify-between">
                            <span class="text-slate-300 font-medium">{{ $d['deduction_name'] }}</span>
                            <span class="font-mono text-rose-400">${{ number_format($d['amount'], 2) }}</span>
                        </div>
                        @endforeach
                    @endif
                    <div class="p-3 flex items-center justify-between">
                        <span class="text-slate-300 font-medium">Statutory Income Tax</span>
                        <span class="font-mono text-rose-400">${{ number_format($payslip->total_tax, 2) }}</span>
                    </div>
                    <div class="p-3 bg-slate-900 flex items-center justify-between font-bold">
                        <span class="text-white">Total Deductions</span>
                        <span class="font-mono text-rose-400">${{ number_format($payslip->total_deductions, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Net Pay Highlight -->
        <div class="bg-gradient-to-r from-emerald-950/40 to-slate-900 border border-emerald-500/30 rounded-2xl p-6 flex items-center justify-between">
            <div>
                <span class="text-xs text-emerald-400 uppercase tracking-wider font-semibold">Net Payout Amount</span>
                <p class="text-xs text-slate-400 mt-0.5">Calculated using deterministic IEEE-compliant financial rounding</p>
            </div>
            <div class="text-right">
                <span class="text-3xl font-black text-emerald-400 font-mono">${{ number_format($payslip->net_pay, 2) }}</span>
            </div>
        </div>
    </div>
</div>
@endsection
