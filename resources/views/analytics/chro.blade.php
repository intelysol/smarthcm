@extends('analytics.layout')

@section('title', 'CHRO Executive Dashboard — Flow HCM')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between border-b border-slate-200 pb-5">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight flex items-center">
                <i class="fa-solid fa-gauge-high text-indigo-600 mr-3"></i>CHRO Executive Dashboard
            </h1>
            <p class="text-sm text-slate-500 mt-1">Cross-functional enterprise workforce, turnover, talent, and payroll intelligence.</p>
        </div>
        <div class="mt-4 md:mt-0 flex items-center space-x-3 text-sm">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                <span class="w-2 h-2 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>Live Snapshot ({{ $data['as_of_date'] ?? now()->toDateString() }})
            </span>
            <button onclick="window.print()" class="px-3 py-1.5 bg-white border border-slate-300 rounded-md shadow-sm text-slate-700 hover:bg-slate-50 transition">
                <i class="fa-solid fa-file-pdf mr-1.5 text-rose-500"></i>Export PDF
            </button>
        </div>
    </div>

    <!-- Executive KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Active Headcount -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Active Workforce</span>
                <span class="p-2 bg-blue-50 text-blue-600 rounded-lg"><i class="fa-solid fa-users"></i></span>
            </div>
            <div class="mt-4">
                <span class="text-3xl font-extrabold text-slate-900">{{ $data['headcount']['active_headcount'] ?? 0 }}</span>
                <span class="text-xs text-slate-500 ml-1">/ {{ $data['headcount']['total_headcount'] ?? 0 }} Total</span>
            </div>
            <div class="mt-2 text-xs text-emerald-600 font-medium flex items-center">
                <i class="fa-solid fa-arrow-trend-up mr-1"></i>{{ $data['headcount']['fte_total'] ?? 0 }} Total FTE
            </div>
        </div>

        <!-- Turnover Rate -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Annual Turnover</span>
                <span class="p-2 bg-amber-50 text-amber-600 rounded-lg"><i class="fa-solid fa-arrow-right-from-bracket"></i></span>
            </div>
            <div class="mt-4">
                <span class="text-3xl font-extrabold text-slate-900">{{ $data['turnover']['turnover_rate_percent'] ?? 0 }}%</span>
                <span class="text-xs text-slate-500 ml-1">YTD</span>
            </div>
            <div class="mt-2 text-xs text-slate-500 font-medium">
                {{ $data['turnover']['voluntary_exits'] ?? 0 }} Voluntary | {{ $data['turnover']['involuntary_exits'] ?? 0 }} Involuntary
            </div>
        </div>

        <!-- Attendance Rate -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Attendance Adherence</span>
                <span class="p-2 bg-emerald-50 text-emerald-600 rounded-lg"><i class="fa-solid fa-clipboard-check"></i></span>
            </div>
            <div class="mt-4">
                <span class="text-3xl font-extrabold text-slate-900">{{ $data['attendance']['attendance_rate_percent'] ?? 100 }}%</span>
            </div>
            <div class="mt-2 text-xs text-emerald-600 font-medium flex items-center">
                <i class="fa-solid fa-clock mr-1"></i>{{ $data['attendance']['total_overtime_hours'] ?? 0 }} Overtime Hours
            </div>
        </div>

        <!-- Employee Engagement -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">eNPS Score</span>
                <span class="p-2 bg-purple-50 text-purple-600 rounded-lg"><i class="fa-solid fa-heart"></i></span>
            </div>
            <div class="mt-4">
                <span class="text-3xl font-extrabold text-slate-900">+{{ $data['engagement']['enps_score'] ?? 42 }}</span>
            </div>
            <div class="mt-2 text-xs text-purple-600 font-medium">
                {{ $data['engagement']['favorability_percent'] ?? 78.5 }}% Favorability Rate
            </div>
        </div>
    </div>

    <!-- Analytical Details Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Department Breakdown -->
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
            <h3 class="text-base font-bold text-slate-900 mb-4 flex items-center">
                <i class="fa-solid fa-sitemap mr-2 text-indigo-500"></i>Headcount by Department
            </h3>
            <div class="space-y-3">
                @forelse($data['headcount']['by_department'] ?? [] as $dept => $cnt)
                <div>
                    <div class="flex justify-between text-xs font-semibold text-slate-700 mb-1">
                        <span>{{ $dept }}</span>
                        <span>{{ $cnt }} Employees</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2">
                        <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ min(100, ($cnt / max(1, $data['headcount']['total_headcount'] ?? 1)) * 100) }}%"></div>
                    </div>
                </div>
                @empty
                <p class="text-xs text-slate-400 italic">No departmental snapshot data available.</p>
                @endforelse
            </div>
        </div>

        <!-- Recruitment Pipeline Summary -->
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
            <h3 class="text-base font-bold text-slate-900 mb-4 flex items-center">
                <i class="fa-solid fa-filter mr-2 text-amber-500"></i>Talent Acquisition Funnel
            </h3>
            <div class="space-y-3">
                @foreach($data['recruitment']['funnel'] ?? [] as $stage)
                <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg border border-slate-100 text-sm">
                    <span class="font-medium text-slate-800">{{ $stage['stage'] }}</span>
                    <div class="text-right">
                        <span class="font-bold text-slate-900">{{ $stage['count'] }}</span>
                        <span class="text-xs text-slate-500 ml-1">({{ $stage['conversion_rate'] }}%)</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

    </div>

</div>
@endsection
