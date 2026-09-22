@extends('shells.executive')

@section('title', 'Total Workforce Cost')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-white">Total Workforce Cost Analytics</h1>
            <p class="text-xs text-slate-400">Aggregated payroll spend, statutory benefits, bonus pools, and contractor cost</p>
        </div>
        <a href="{{ route('executive.overview') }}" class="text-xs font-semibold text-[#C9A227] hover:underline">&larr; Back to Strategic Overview</a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 shadow">
            <span class="text-xs font-medium text-slate-400">Total Compensation Runrate</span>
            <div class="mt-2 text-2xl font-black text-white">{{ $costKpis['total_compensation_runrate'] ?? '$0.00' }}</div>
            <p class="text-xs text-slate-400 mt-1">Monthly gross cost</p>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 shadow">
            <span class="text-xs font-medium text-slate-400">Benefits &amp; Insurance</span>
            <div class="mt-2 text-2xl font-black text-white">{{ $costKpis['benefits_and_insurance'] ?? '$0.00' }}</div>
            <p class="text-xs text-slate-400 mt-1">{{ $costKpis['benefits_percentage'] ?? '0.0% of base pay' }}</p>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 shadow">
            <span class="text-xs font-medium text-slate-400">Overtime Spend</span>
            <div class="mt-2 text-2xl font-black text-emerald-400">{{ $costKpis['overtime_spend'] ?? '$0.00' }}</div>
            <p class="text-xs text-slate-400 mt-1">Controlled variance</p>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 shadow">
            <span class="text-xs font-medium text-slate-400">Revenue per Employee</span>
            <div class="mt-2 text-2xl font-black text-[#C9A227]">{{ $costKpis['revenue_per_employee'] ?? 'Data unavailable' }}</div>
            <p class="text-xs text-emerald-400 mt-1">Productivity multiplier</p>
        </div>
    </div>
</div>
@endsection
