@extends('analytics.layout')

@section('title', 'HCM Standard Reports Catalog — Flow HCM')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between border-b border-slate-200 pb-5">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight flex items-center">
                <i class="fa-solid fa-file-lines text-pink-600 mr-3"></i>HCM Reports & Datasets Catalog
            </h1>
            <p class="text-sm text-slate-500 mt-1">Operational and historical datasets ready for querying and automated distribution.</p>
        </div>
        <div class="mt-4 md:mt-0">
            <a href="{{ route('hcm.analytics.reports.builder') }}" class="px-4 py-2 bg-pink-600 hover:bg-pink-700 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                <i class="fa-solid fa-plus mr-1.5"></i>Custom Report Builder
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm hover:border-pink-300 transition">
            <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-lg mb-4">
                <i class="fa-solid fa-users"></i>
            </div>
            <h3 class="font-bold text-slate-900 text-base mb-1">Workforce & Headcount Snapshot</h3>
            <p class="text-xs text-slate-500 mb-4">Point-in-time employee active counts, FTE, department breakdowns, and tenure.</p>
            <a href="{{ route('hcm.analytics.reports.builder', ['dataset' => 'DP_WORKFORCE']) }}" class="text-xs font-bold text-blue-600 hover:text-blue-800">Query Dataset &rarr;</a>
        </div>

        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm hover:border-pink-300 transition">
            <div class="w-10 h-10 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-lg mb-4">
                <i class="fa-solid fa-clock"></i>
            </div>
            <h3 class="font-bold text-slate-900 text-base mb-1">Monthly Attendance & Overtime</h3>
            <p class="text-xs text-slate-500 mb-4">Punctuality rates, total working hours, overtime hours, and absence trends.</p>
            <a href="{{ route('hcm.analytics.reports.builder', ['dataset' => 'DP_ATTENDANCE']) }}" class="text-xs font-bold text-emerald-600 hover:text-emerald-800">Query Dataset &rarr;</a>
        </div>

        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm hover:border-pink-300 transition">
            <div class="w-10 h-10 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center font-bold text-lg mb-4">
                <i class="fa-solid fa-coins"></i>
            </div>
            <h3 class="font-bold text-slate-900 text-base mb-1">Payroll & Statutory Costs</h3>
            <p class="text-xs text-slate-500 mb-4">Earnings, tax withholding, employer contributions, and net payout summaries.</p>
            <a href="{{ route('hcm.analytics.reports.builder', ['dataset' => 'DP_PAYROLL']) }}" class="text-xs font-bold text-purple-600 hover:text-purple-800">Query Dataset &rarr;</a>
        </div>
    </div>
</div>
@endsection
