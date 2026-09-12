@extends('analytics.layout')

@section('title', 'Payroll & Compensation Analytics — Flow HCM')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between border-b border-slate-200 pb-5">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight flex items-center">
                <i class="fa-solid fa-coins text-purple-600 mr-3"></i>Payroll & Compensation Analytics
            </h1>
            <p class="text-sm text-slate-500 mt-1">Confidential cost analysis, employer statutory contributions, salary distribution percentiles, and compa-ratios.</p>
        </div>
        <div class="mt-4 md:mt-0">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-800">
                <i class="fa-solid fa-lock mr-1.5 text-purple-600"></i>Confidential Financial Access
            </span>
        </div>
    </div>

    <!-- Payroll Totals Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-xs font-semibold uppercase text-slate-500">Gross Payroll Cost</span>
            <div class="mt-3 text-3xl font-extrabold text-purple-700">${{ number_format($summary['gross_payroll'] ?? 0, 2) }}</div>
            <p class="text-xs text-slate-500 mt-1">{{ $summary['employees_paid_count'] ?? 0 }} employees paid in cycle</p>
        </div>
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-xs font-semibold uppercase text-slate-500">Net Take-Home Pay</span>
            <div class="mt-3 text-3xl font-extrabold text-emerald-600">${{ number_format($summary['net_payroll'] ?? 0, 2) }}</div>
            <p class="text-xs text-slate-500 mt-1">Disbursed net salary</p>
        </div>
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-xs font-semibold uppercase text-slate-500">Employer Cost</span>
            <div class="mt-3 text-3xl font-extrabold text-indigo-600">${{ number_format($summary['employer_cost'] ?? 0, 2) }}</div>
            <p class="text-xs text-slate-500 mt-1">Statutory contributions & benefits</p>
        </div>
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-xs font-semibold uppercase text-slate-500">Average Salary</span>
            <div class="mt-3 text-3xl font-extrabold text-slate-900">${{ number_format($distribution['mean'] ?? 0, 2) }}</div>
            <p class="text-xs text-slate-500 mt-1">Median: ${{ number_format($distribution['median'] ?? 0, 2) }}</p>
        </div>
    </div>

    <!-- Salary Percentile Breakdown -->
    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
        <h3 class="font-bold text-slate-900 text-sm mb-4">Salary Distribution & Percentiles</h3>
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 text-center">
            <div class="p-3 bg-slate-50 rounded-lg border border-slate-100">
                <span class="text-xs text-slate-500">Minimum</span>
                <div class="font-bold text-slate-800 text-lg mt-1">${{ number_format($distribution['min'] ?? 0, 0) }}</div>
            </div>
            <div class="p-3 bg-slate-50 rounded-lg border border-slate-100">
                <span class="text-xs text-slate-500">25th Percentile (P25)</span>
                <div class="font-bold text-slate-800 text-lg mt-1">${{ number_format($distribution['p25'] ?? 0, 0) }}</div>
            </div>
            <div class="p-3 bg-purple-50 rounded-lg border border-purple-100">
                <span class="text-xs text-purple-700 font-semibold">Median (P50)</span>
                <div class="font-bold text-purple-900 text-lg mt-1">${{ number_format($distribution['median'] ?? 0, 0) }}</div>
            </div>
            <div class="p-3 bg-slate-50 rounded-lg border border-slate-100">
                <span class="text-xs text-slate-500">75th Percentile (P75)</span>
                <div class="font-bold text-slate-800 text-lg mt-1">${{ number_format($distribution['p75'] ?? 0, 0) }}</div>
            </div>
            <div class="p-3 bg-slate-50 rounded-lg border border-slate-100">
                <span class="text-xs text-slate-500">Maximum</span>
                <div class="font-bold text-slate-800 text-lg mt-1">${{ number_format($distribution['max'] ?? 0, 0) }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
