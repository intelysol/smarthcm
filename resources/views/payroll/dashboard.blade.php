@extends('payroll.layout')

@section('title', 'Payroll Dashboard')
@section('page_title', 'Payroll & Compensation Control Center')

@section('content')
<div class="space-y-8">
    <!-- Stat Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-slate-950 border border-slate-800 rounded-xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-slate-400">Active Periods</span>
                <div class="w-8 h-8 rounded-lg bg-blue-500/10 text-blue-400 flex items-center justify-center">
                    <i class="fa-regular fa-calendar-check"></i>
                </div>
            </div>
            <p class="mt-4 text-2xl font-bold text-white">{{ $periods->count() }}</p>
            <p class="mt-1 text-xs text-slate-400">Current calendar schedules</p>
        </div>

        <div class="bg-slate-950 border border-slate-800 rounded-xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-slate-400">Payroll Runs</span>
                <div class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center">
                    <i class="fa-solid fa-calculator"></i>
                </div>
            </div>
            <p class="mt-4 text-2xl font-bold text-white">{{ $runs->count() }}</p>
            <p class="mt-1 text-xs text-slate-400">Recent calculation batches</p>
        </div>

        <div class="bg-slate-950 border border-slate-800 rounded-xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-slate-400">Pending Adjustments</span>
                <div class="w-8 h-8 rounded-lg bg-purple-500/10 text-purple-400 flex items-center justify-center">
                    <i class="fa-solid fa-sliders"></i>
                </div>
            </div>
            <p class="mt-4 text-2xl font-bold text-white">{{ $adjustments }}</p>
            <p class="mt-1 text-xs text-slate-400">Awaiting HR manager review</p>
        </div>

        <div class="bg-slate-950 border border-slate-800 rounded-xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-slate-400">Draft Payments</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                    <i class="fa-solid fa-building-columns"></i>
                </div>
            </div>
            <p class="mt-4 text-2xl font-bold text-white">{{ $batches }}</p>
            <p class="mt-1 text-xs text-slate-400">Pending bank disbursement</p>
        </div>
    </div>

    <!-- Active Runs & Periods Tables -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Recent Runs -->
        <div class="bg-slate-950 border border-slate-800 rounded-xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-semibold text-white">Recent Payroll Runs</h3>
                <a href="{{ route('payroll.runs.index') }}" class="text-xs text-emerald-400 hover:underline">View all</a>
            </div>
            <div class="divide-y divide-slate-800">
                @forelse($runs as $r)
                <div class="py-3 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-white">{{ $r->name }}</p>
                        <p class="text-xs text-slate-400">{{ $r->run_number }} &bull; {{ $r->employee_count }} employees</p>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $r->status === 'approved' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-slate-800 text-slate-300' }}">
                            {{ strtoupper($r->status) }}
                        </span>
                        <p class="text-xs font-mono text-slate-300 mt-1">${{ number_format($r->net_total, 2) }}</p>
                    </div>
                </div>
                @empty
                <p class="text-sm text-slate-500 py-4 text-center">No payroll runs yet.</p>
                @endforelse
            </div>
        </div>

        <!-- Recent Periods -->
        <div class="bg-slate-950 border border-slate-800 rounded-xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-semibold text-white">Payroll Periods</h3>
                <a href="{{ route('payroll.periods.index') }}" class="text-xs text-emerald-400 hover:underline">View all</a>
            </div>
            <div class="divide-y divide-slate-800">
                @forelse($periods as $p)
                <div class="py-3 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-white">{{ $p->period_name }}</p>
                        <p class="text-xs text-slate-400">{{ $p->start_date->format('M d') }} - {{ $p->end_date->format('M d, Y') }}</p>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $p->isLocked() ? 'bg-rose-500/10 text-rose-400 border border-rose-500/20' : 'bg-blue-500/10 text-blue-400 border border-blue-500/20' }}">
                            {{ strtoupper($p->status) }}
                        </span>
                    </div>
                </div>
                @empty
                <p class="text-sm text-slate-500 py-4 text-center">No payroll periods configured.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
