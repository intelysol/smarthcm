@extends('shells.manager')

@section('title', 'Team Analytics')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Team Analytics &amp; Metrics</h1>
            <p class="text-xs text-slate-500">Aggregated attendance, overtime, leave utilization, and team capacity</p>
        </div>
        <a href="{{ route('manager.workbench') }}" class="text-xs font-semibold text-[#1E3A5F] hover:underline">&larr; Back to Workbench</a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Attendance Rate</span>
            <div class="mt-2 text-2xl font-black text-slate-900">98.5%</div>
            <p class="text-xs text-emerald-600 mt-1 font-semibold">+1.2% this month</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Avg Overtime</span>
            <div class="mt-2 text-2xl font-black text-slate-900">3.2 hrs</div>
            <p class="text-xs text-slate-500 mt-1">Within normal threshold</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Leave Utilization</span>
            <div class="mt-2 text-2xl font-black text-slate-900">62.0%</div>
            <p class="text-xs text-slate-500 mt-1">Annual allowance utilized</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Flight Risk</span>
            <div class="mt-2 text-2xl font-black text-emerald-600">Low</div>
            <p class="text-xs text-slate-500 mt-1">High team satisfaction score</p>
        </div>
    </div>
</div>
@endsection
