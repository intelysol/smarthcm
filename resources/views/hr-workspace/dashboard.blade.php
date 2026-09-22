@extends('shells.hr')

@section('title', 'HR Command Center')

@section('content')
<div class="space-y-6">

    <!-- Hero Banner -->
    <div class="bg-[#1E3A5F] text-white rounded-2xl p-6 shadow-md flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-mono text-[#F4E7B2] font-semibold uppercase tracking-wider mb-1">
                <i class="fa-solid fa-tower-broadcast text-[#C9A227]"></i>
                <span>HR Operations Command Center</span>
            </div>
            <h1 class="text-2xl font-black tracking-tight">Workforce Operations & Governance</h1>
            <p class="text-xs text-slate-300 mt-0.5">Central hub for talent acquisition, employee lifecycle, payroll processing, and attendance.</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ Route::has('recruitment.requisitions.index') ? route('recruitment.requisitions.index') : url('/recruitment/requisitions') }}" class="px-3.5 py-2 rounded-xl bg-[#C9A227] hover:bg-[#b58f1f] text-[#142A44] font-bold text-xs shadow transition flex items-center">
                <i class="fa-solid fa-user-plus mr-1.5"></i> New Requisition
            </a>
            <a href="{{ Route::has('payroll.dashboard') ? route('payroll.dashboard') : url('/payroll/dashboard') }}" class="px-3.5 py-2 rounded-xl bg-[#142A44] hover:bg-[#0d1c2e] border border-white/20 text-white font-semibold text-xs transition flex items-center">
                <i class="fa-solid fa-file-invoice-dollar mr-1.5 text-[#C9A227]"></i> Payroll Runs
            </a>
        </div>
    </div>

    <!-- HR KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Headcount</span>
                <span class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-users"></i>
                </span>
            </div>
            <div class="mt-2 flex items-baseline space-x-2">
                <span class="text-2xl font-black text-slate-900">{{ number_format($metrics['total_headcount']) }}</span>
                <span class="text-xs text-slate-400 font-medium">active staff</span>
            </div>
            <div class="mt-2 text-[11px] text-slate-500">
                <span>99.2% profile completeness</span>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Pending Leaves</span>
                <span class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-calendar-minus"></i>
                </span>
            </div>
            <div class="mt-2 flex items-baseline space-x-2">
                <span class="text-2xl font-black text-amber-600">{{ $metrics['pending_leaves'] }}</span>
                <span class="text-xs text-slate-400 font-medium">requests</span>
            </div>
            <div class="mt-2 text-[11px] text-slate-500">
                <a href="{{ Route::has('portal.requests') ? route('portal.requests') : url('/portal/requests') }}" class="text-[#1E3A5F] font-semibold hover:underline">Review applications &rarr;</a>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Open Requisitions</span>
                <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-briefcase"></i>
                </span>
            </div>
            <div class="mt-2 flex items-baseline space-x-2">
                <span class="text-2xl font-black text-slate-900">{{ $metrics['active_requisitions'] }}</span>
                <span class="text-xs text-slate-400 font-medium">positions</span>
            </div>
            <div class="mt-2 text-[11px] text-emerald-700 flex items-center">
                <i class="fa-solid fa-circle-check mr-1 text-[10px]"></i> Hiring pipeline active
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Employee Relations</span>
                <span class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-handshake-angle"></i>
                </span>
            </div>
            <div class="mt-2 flex items-baseline space-x-2">
                <span class="text-2xl font-black text-slate-900">{{ $metrics['open_cases'] }}</span>
                <span class="text-xs text-slate-400 font-medium">active cases</span>
            </div>
            <div class="mt-2 text-[11px] text-slate-500">
                <span>Resolution SLA compliant</span>
            </div>
        </div>
    </div>

    <!-- Quick Operations Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm space-y-3">
            <div class="flex items-center space-x-3">
                <div class="w-9 h-9 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <i class="fa-solid fa-clock"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Time &amp; Attendance</h3>
                    <p class="text-xs text-slate-500">Monitor shifts, biometric sync, and roster.</p>
                </div>
            </div>
            <div class="pt-2">
                <a href="{{ Route::has('hcm.attendance.dashboard') ? route('hcm.attendance.dashboard') : url('/attendance/dashboard') }}" class="text-xs font-semibold text-[#1E3A5F] hover:underline flex items-center">
                    Attendance Dashboard &rarr;
                </a>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm space-y-3">
            <div class="flex items-center space-x-3">
                <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Payroll Processing</h3>
                    <p class="text-xs text-slate-500">Execute payroll runs, deductions, and tax slips.</p>
                </div>
            </div>
            <div class="pt-2">
                <a href="{{ Route::has('payroll.dashboard') ? route('payroll.dashboard') : url('/payroll/dashboard') }}" class="text-xs font-semibold text-[#1E3A5F] hover:underline flex items-center">
                    Manage Payroll Runs &rarr;
                </a>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm space-y-3">
            <div class="flex items-center space-x-3">
                <div class="w-9 h-9 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i class="fa-solid fa-bullseye"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Performance &amp; Goals</h3>
                    <p class="text-xs text-slate-500">Track company appraisals and 360 review cycles.</p>
                </div>
            </div>
            <div class="pt-2">
                <a href="{{ Route::has('performance.cycles.index') ? route('performance.cycles.index') : url('/performance/cycles') }}" class="text-xs font-semibold text-[#1E3A5F] hover:underline flex items-center">
                    Review Cycles &rarr;
                </a>
            </div>
        </div>
    </div>

</div>
@endsection
