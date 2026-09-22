@extends('shells.manager')

@section('title', 'Team Performance')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Team Performance &amp; Reviews</h1>
            <p class="text-xs text-slate-500">Track goal progress, 1-on-1s, and upcoming review cycles for your direct reports</p>
        </div>
        <a href="{{ route('manager.workbench') }}" class="text-xs font-semibold text-[#1E3A5F] hover:underline">&larr; Back to Workbench</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Active Review Cycle</span>
            <div class="mt-2 text-lg font-bold text-slate-900">Q3 Enterprise Appraisal</div>
            <p class="text-xs text-slate-500 mt-1">Due in 14 days &bull; 80% submission rate</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Team Goals Tracked</span>
            <div class="mt-2 text-lg font-bold text-slate-900">12 Objectives</div>
            <p class="text-xs text-emerald-600 mt-1 font-semibold">9 on track, 3 requiring review</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">1-on-1 Health</span>
            <div class="mt-2 text-lg font-bold text-slate-900">All Scheduled</div>
            <p class="text-xs text-slate-500 mt-1">Bi-weekly cadence maintained</p>
        </div>
    </div>
</div>
@endsection
