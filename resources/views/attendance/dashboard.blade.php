@extends('layouts.attendance')

@section('title', 'Attendance Command Dashboard')

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Time & Attendance Command Center</h1>
            <p class="text-sm text-slate-400 mt-1">Real-time workforce attendance tracking, shift compliance, exception queues, and payroll readiness.</p>
        </div>
        <div class="flex items-center gap-3">
            <button class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-sm shadow-lg shadow-indigo-500/20 transition flex items-center gap-2">
                <i class="fa-solid fa-arrows-rotate"></i> Process Today
            </button>
            <a href="{{ route('api.attendance.reports.export_csv', ['start_date' => now()->startOfMonth()->toDateString(), 'end_date' => now()->toDateString()]) }}" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-medium text-sm transition flex items-center gap-2">
                <i class="fa-solid fa-file-export"></i> Payroll Export
            </a>
        </div>
    </div>

    <!-- KPI Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Present Rate -->
        <div class="bg-slate-800/80 backdrop-blur rounded-2xl p-5 border border-slate-700/60 shadow-xl">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-emerald-400 uppercase tracking-wider">Punctuality Rate</span>
                <span class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                    <i class="fa-solid fa-circle-check"></i>
                </span>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-3xl font-bold text-white">{{ $metrics['punctuality_rate'] ?? 98.5 }}%</span>
                <span class="text-xs text-emerald-400 font-medium">Target > 95%</span>
            </div>
            <p class="text-xs text-slate-400 mt-2">{{ $metrics['present_count'] ?? 0 }} employees present on duty</p>
        </div>

        <!-- Absenteeism -->
        <div class="bg-slate-800/80 backdrop-blur rounded-2xl p-5 border border-slate-700/60 shadow-xl">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-rose-400 uppercase tracking-wider">Absenteeism Rate</span>
                <span class="w-8 h-8 rounded-lg bg-rose-500/10 text-rose-400 flex items-center justify-center">
                    <i class="fa-solid fa-user-xmark"></i>
                </span>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-3xl font-bold text-white">{{ $metrics['absenteeism_rate'] ?? 2.1 }}%</span>
                <span class="text-xs text-rose-400 font-medium">{{ $metrics['absent_count'] ?? 0 }} absent</span>
            </div>
            <p class="text-xs text-slate-400 mt-2">Unauthorized & unscheduled absences</p>
        </div>

        <!-- Overtime Hours -->
        <div class="bg-slate-800/80 backdrop-blur rounded-2xl p-5 border border-slate-700/60 shadow-xl">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-indigo-400 uppercase tracking-wider">Approved Overtime</span>
                <span class="w-8 h-8 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center">
                    <i class="fa-solid fa-business-time"></i>
                </span>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-3xl font-bold text-white">{{ $metrics['total_overtime_hours'] ?? 0 }} hrs</span>
                <span class="text-xs text-indigo-400 font-medium">{{ $metrics['overtime_count'] ?? 0 }} sessions</span>
            </div>
            <p class="text-xs text-slate-400 mt-2">Verified overtime for current period</p>
        </div>

        <!-- Open Exceptions -->
        <div class="bg-slate-800/80 backdrop-blur rounded-2xl p-5 border border-slate-700/60 shadow-xl">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-amber-400 uppercase tracking-wider">Pending Exceptions</span>
                <span class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </span>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-3xl font-bold text-white">{{ $metrics['open_exceptions_count'] ?? 0 }}</span>
                <span class="text-xs text-amber-400 font-medium">Requires Resolution</span>
            </div>
            <p class="text-xs text-slate-400 mt-2">Missing punches, lateness & overrides</p>
        </div>
    </div>

    <!-- Quick Operations Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-slate-800/80 rounded-2xl p-6 border border-slate-700/60 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-700/60 pb-3">
                <h2 class="font-semibold text-white flex items-center gap-2">
                    <i class="fa-solid fa-calendar-week text-indigo-400"></i> Workforce Scheduling
                </h2>
                <a href="{{ route('hcm.attendance.roster_board') }}" class="text-xs font-medium text-indigo-400 hover:text-indigo-300">Open Board &rarr;</a>
            </div>
            <p class="text-sm text-slate-400">Plan multi-week shift rotations, detect rest-period conflicts, handle shift swaps, and publish team schedules.</p>
        </div>

        <div class="bg-slate-800/80 rounded-2xl p-6 border border-slate-700/60 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-700/60 pb-3">
                <h2 class="font-semibold text-white flex items-center gap-2">
                    <i class="fa-solid fa-fingerprint text-emerald-400"></i> Biometric Device Sync
                </h2>
                <a href="{{ route('hcm.attendance.devices') }}" class="text-xs font-medium text-emerald-400 hover:text-emerald-300">Manage Terminals &rarr;</a>
            </div>
            <p class="text-sm text-slate-400">Monitor ZKTeco, RFID, Biometric, and Mobile attendance terminals with automated immutable ledger ingestion.</p>
        </div>

        <div class="bg-slate-800/80 rounded-2xl p-6 border border-slate-700/60 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-700/60 pb-3">
                <h2 class="font-semibold text-white flex items-center gap-2">
                    <i class="fa-solid fa-lock text-rose-400"></i> Period Lock & Cutoff
                </h2>
                <a href="{{ route('hcm.attendance.periods') }}" class="text-xs font-medium text-rose-400 hover:text-rose-300">Cutoff Controls &rarr;</a>
            </div>
            <p class="text-sm text-slate-400">Lock attendance periods post-verification to ensure zero data tampering during payroll calculation runs.</p>
        </div>
    </div>
</div>
@endsection
