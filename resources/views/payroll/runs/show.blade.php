@extends('payroll.layout')

@section('title', 'Payroll Run Details')
@section('page_title', "Payroll Run: {$run->name}")

@section('content')
<div class="space-y-8">
    <!-- Header Summary Bar -->
    <div class="bg-slate-950 border border-slate-800 rounded-xl p-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-3">
                <h3 class="text-xl font-bold text-white">{{ $run->name }}</h3>
                <span class="inline-flex px-2.5 py-1 rounded text-xs font-semibold {{ $run->status === 'approved' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20' }}">
                    {{ strtoupper($run->status) }}
                </span>
            </div>
            <p class="text-xs text-slate-400 mt-1 font-mono">Run #{{ $run->run_number }} &bull; Period: {{ $run->period?->period_name }}</p>
        </div>

        <div class="flex items-center space-x-3">
            @if($run->status === 'calculated' || $run->status === 'under_review')
            <button class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-medium text-xs shadow-lg shadow-emerald-600/30 transition">
                <i class="fa-solid fa-check mr-1.5"></i> Approve Run
            </button>
            @endif
        </div>
    </div>

    <!-- Financial KPIs -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div class="bg-slate-950 border border-slate-800 rounded-xl p-4">
            <span class="text-xs text-slate-400">Total Employees</span>
            <p class="text-xl font-bold text-white mt-1">{{ $kpis['total_employees'] }}</p>
        </div>
        <div class="bg-slate-950 border border-slate-800 rounded-xl p-4">
            <span class="text-xs text-slate-400">Gross Salary</span>
            <p class="text-xl font-bold text-white mt-1 font-mono">${{ number_format($kpis['total_gross'], 2) }}</p>
        </div>
        <div class="bg-slate-950 border border-slate-800 rounded-xl p-4">
            <span class="text-xs text-slate-400">Total Taxes</span>
            <p class="text-xl font-bold text-rose-400 mt-1 font-mono">${{ number_format($kpis['total_tax'], 2) }}</p>
        </div>
        <div class="bg-slate-950 border border-slate-800 rounded-xl p-4">
            <span class="text-xs text-slate-400">Employer Cost</span>
            <p class="text-xl font-bold text-indigo-400 mt-1 font-mono">${{ number_format($kpis['total_employer_cost'], 2) }}</p>
        </div>
        <div class="bg-slate-950 border border-slate-800 rounded-xl p-4">
            <span class="text-xs text-slate-400">Net Payable</span>
            <p class="text-xl font-bold text-emerald-400 mt-1 font-mono">${{ number_format($kpis['total_net'], 2) }}</p>
        </div>
    </div>

    <!-- Exceptions & Variances Alerts -->
    @if($run->exceptions->isNotEmpty())
    <div class="bg-rose-950/20 border border-rose-900/50 rounded-xl p-5">
        <h4 class="text-sm font-bold text-rose-400 mb-3"><i class="fa-solid fa-triangle-exclamation mr-2"></i> Payroll Exceptions Flagged</h4>
        <div class="space-y-2">
            @foreach($run->exceptions as $ex)
            <div class="text-xs flex items-center justify-between text-rose-300">
                <span>{{ $ex->message }}</span>
                <span class="font-mono text-[10px] uppercase px-2 py-0.5 rounded bg-rose-900/40">{{ $ex->severity }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Employee Snapshots Table -->
    <div class="bg-slate-950 border border-slate-800 rounded-xl overflow-hidden">
        <div class="p-5 border-b border-slate-800">
            <h4 class="text-base font-semibold text-white">Itemized Calculation Snapshots</h4>
        </div>
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-900/80 border-b border-slate-800 text-slate-400 text-xs uppercase font-medium">
                <tr>
                    <th class="px-6 py-4">Employee</th>
                    <th class="px-6 py-4">Gross Pay</th>
                    <th class="px-6 py-4">Deductions</th>
                    <th class="px-6 py-4">Tax</th>
                    <th class="px-6 py-4">Employer Cost</th>
                    <th class="px-6 py-4">Net Pay</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800 text-slate-300">
                @foreach($run->calculationSnapshots as $snap)
                <tr class="hover:bg-slate-900/50 transition">
                    <td class="px-6 py-4 font-medium text-white">{{ $snap->employee?->fullName() }}</td>
                    <td class="px-6 py-4 font-mono text-xs">${{ number_format($snap->gross_pay, 2) }}</td>
                    <td class="px-6 py-4 font-mono text-xs">${{ number_format($snap->total_deductions, 2) }}</td>
                    <td class="px-6 py-4 font-mono text-xs">${{ number_format($snap->total_tax, 2) }}</td>
                    <td class="px-6 py-4 font-mono text-xs">${{ number_format($snap->total_employer_cost, 2) }}</td>
                    <td class="px-6 py-4 font-mono text-xs font-bold text-emerald-400">${{ number_format($snap->net_pay, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
