@extends('layouts.attendance')

@section('title', 'Team Attendance & Approvals')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Team Attendance & Approvals Portal</h1>
            <p class="text-sm text-slate-400 mt-1">Real-time team presence tracking, regularizations approval, and timesheet sign-offs.</p>
        </div>
    </div>

    <!-- Approvals Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="bg-slate-800/80 rounded-2xl p-5 border border-slate-700/60 shadow-xl">
            <span class="text-xs font-semibold text-amber-400 uppercase tracking-wider">Pending Adjustments</span>
            <div class="text-3xl font-bold text-white mt-3">3</div>
            <p class="text-xs text-slate-400 mt-1">Missed punch & regularization requests</p>
        </div>
        <div class="bg-slate-800/80 rounded-2xl p-5 border border-slate-700/60 shadow-xl">
            <span class="text-xs font-semibold text-indigo-400 uppercase tracking-wider">Overtime Requests</span>
            <div class="text-3xl font-bold text-white mt-3">1</div>
            <p class="text-xs text-slate-400 mt-1">Pending manager pre/post sign-off</p>
        </div>
        <div class="bg-slate-800/80 rounded-2xl p-5 border border-slate-700/60 shadow-xl">
            <span class="text-xs font-semibold text-emerald-400 uppercase tracking-wider">Submitted Timesheets</span>
            <div class="text-3xl font-bold text-white mt-3">12</div>
            <p class="text-xs text-slate-400 mt-1">Ready for periodic approval</p>
        </div>
    </div>
</div>
@endsection
