@extends('compliance.layout')

@section('title', 'Workforce Compliance Dashboard')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Compliance & Regulatory Health</h2>
            <p class="text-sm text-slate-500">Holistic monitoring of permits, visas, professional licenses, and regulatory obligations across legal entities.</p>
        </div>
        <div class="flex items-center gap-3">
            <button type="button" class="inline-flex items-center px-4 py-2 border border-slate-300 rounded-md shadow-sm text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 focus:outline-none">
                Export Audit Report
            </button>
            <button type="button" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none">
                Re-evaluate All
            </button>
        </div>
    </div>

    <!-- Key Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-5">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Average Compliance Score</div>
            <div class="mt-2 flex items-baseline">
                <span class="text-3xl font-extrabold text-slate-900">{{ $stats['average_score'] ?? 0 }}%</span>
                <span class="ml-2 text-xs font-medium text-slate-500">Operational Index</span>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-5">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Employees Monitored</div>
            <div class="mt-2 flex items-baseline">
                <span class="text-3xl font-extrabold text-slate-900">{{ $stats['total_employees_monitored'] ?? 0 }}</span>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-5">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Full Compliance</div>
            <div class="mt-2 flex items-baseline">
                <span class="text-3xl font-extrabold text-emerald-600">{{ $stats['fully_compliant_count'] ?? 0 }}</span>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-5">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Breached / At-Risk</div>
            <div class="mt-2 flex items-baseline">
                <span class="text-3xl font-extrabold text-rose-600">{{ $stats['breached_count'] ?? 0 }}</span>
            </div>
        </div>
    </div>

    <!-- Expiration Watchlist & Active Items -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
            <h3 class="text-base font-semibold text-slate-800 mb-4">Expiration Horizon</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between p-3 rounded-lg bg-rose-50 border border-rose-200">
                    <span class="text-sm font-medium text-rose-900">Expiring within 30 Days (Critical)</span>
                    <span class="text-base font-bold text-rose-700">{{ $stats['expiring_30_days'] ?? 0 }}</span>
                </div>
                <div class="flex items-center justify-between p-3 rounded-lg bg-amber-50 border border-amber-200">
                    <span class="text-sm font-medium text-amber-900">Expiring within 60 Days (Warning)</span>
                    <span class="text-base font-bold text-amber-700">{{ $stats['expiring_60_days'] ?? 0 }}</span>
                </div>
                <div class="flex items-center justify-between p-3 rounded-lg bg-indigo-50 border border-indigo-200">
                    <span class="text-sm font-medium text-indigo-900">Expiring within 90 Days (Notice)</span>
                    <span class="text-base font-bold text-indigo-700">{{ $stats['expiring_90_days'] ?? 0 }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
            <h3 class="text-base font-semibold text-slate-800 mb-4">Active Compliance Portfolios</h3>
            <div class="grid grid-cols-2 gap-4">
                <div class="p-4 rounded-lg bg-slate-50 border border-slate-200">
                    <div class="text-xs text-slate-500 font-medium">Work Permits</div>
                    <div class="text-2xl font-bold text-slate-800 mt-1">{{ $stats['active_work_permits'] ?? 0 }}</div>
                </div>
                <div class="p-4 rounded-lg bg-slate-50 border border-slate-200">
                    <div class="text-xs text-slate-500 font-medium">Visa & Residency</div>
                    <div class="text-2xl font-bold text-slate-800 mt-1">{{ $stats['active_visas'] ?? 0 }}</div>
                </div>
                <div class="p-4 rounded-lg bg-slate-50 border border-slate-200">
                    <div class="text-xs text-slate-500 font-medium">Professional Licenses</div>
                    <div class="text-2xl font-bold text-slate-800 mt-1">{{ $stats['active_licenses'] ?? 0 }}</div>
                </div>
                <div class="p-4 rounded-lg bg-slate-50 border border-slate-200">
                    <div class="text-xs text-slate-500 font-medium">Active Exemptions</div>
                    <div class="text-2xl font-bold text-slate-800 mt-1">{{ $stats['active_exemptions'] ?? 0 }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
