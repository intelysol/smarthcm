@extends('portal.layout')

@section('title', 'My Pay & Payslips')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-white tracking-wide">My Pay &amp; Compensation</h1>
            <p class="text-xs text-slate-400 mt-1">Access your monthly payroll statements, earnings breakdown, and statutory tax deductions securely.</p>
        </div>
        <span class="px-3 py-1 rounded-lg text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 flex items-center space-x-1.5">
            <i class="fa-solid fa-shield-halved"></i>
            <span>Encrypted Payroll Channel</span>
        </span>
    </div>

    <!-- Compensation Summary Card -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-850 to-slate-900 border border-slate-800 p-6 rounded-2xl shadow-sm grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div>
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Compensation Model</span>
            <div class="text-lg font-bold text-white mt-1">Standard Full-Time Package</div>
            <p class="text-xs text-slate-400 mt-1">Payroll Cycle: Monthly (End of Month)</p>
        </div>
        <div>
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Statutory Deductions</span>
            <div class="text-lg font-bold text-indigo-400 mt-1">Tax &amp; Pension Enrolled</div>
            <p class="text-xs text-slate-400 mt-1">Automatic Withholding Applied</p>
        </div>
        <div>
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Disbursement Method</span>
            <div class="text-lg font-bold text-emerald-400 mt-1">Direct Bank Deposit</div>
            <p class="text-xs text-slate-400 mt-1">Verified Bank Account on File</p>
        </div>
    </div>

    <!-- Historical Payslips Table -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-sm space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="text-sm font-bold text-white flex items-center">
                <i class="fa-solid fa-file-invoice-dollar mr-2 text-emerald-400"></i> Payroll Statements
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="text-[10px] uppercase font-bold text-slate-400 border-b border-slate-800 bg-slate-950/50">
                    <tr>
                        <th class="py-2.5 px-3">Payroll Period</th>
                        <th class="py-2.5 px-3">Gross Earnings</th>
                        <th class="py-2.5 px-3">Deductions</th>
                        <th class="py-2.5 px-3">Net Pay</th>
                        <th class="py-2.5 px-3">Payment Status</th>
                        <th class="py-2.5 px-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($payslips as $slip)
                        <tr class="hover:bg-slate-850/50 transition">
                            <td class="py-3 px-3 font-semibold text-white">{{ $slip->period_name ?? 'Payroll Period' }}</td>
                            <td class="py-3 px-3 font-mono">{{ $slip->currency ?? 'USD' }} {{ number_format($slip->gross_pay ?? 0, 2) }}</td>
                            <td class="py-3 px-3 font-mono text-rose-400">-{{ $slip->currency ?? 'USD' }} {{ number_format($slip->total_deductions ?? 0, 2) }}</td>
                            <td class="py-3 px-3 font-mono font-bold text-emerald-400">{{ $slip->currency ?? 'USD' }} {{ number_format($slip->net_pay ?? 0, 2) }}</td>
                            <td class="py-3 px-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-400">
                                    PAID
                                </span>
                            </td>
                            <td class="py-3 px-3 text-right">
                                <a href="/api/me/pay/payslips/{{ $slip->id }}" target="_blank" class="px-3 py-1 bg-slate-800 hover:bg-slate-700 text-indigo-300 rounded font-medium text-[11px] transition">
                                    <i class="fa-solid fa-download mr-1"></i> View Statement
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-500">
                                <i class="fa-solid fa-file-invoice text-2xl text-slate-600 mb-2 block"></i>
                                No published payslips available for download yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
