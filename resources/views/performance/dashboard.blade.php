@extends('performance.layout')

@section('title', 'Performance Dashboard')

@section('content')
<div class=space-y-8>
    <div>
        <h1 class=text-2xl font-bold tracking-tight text-white>Performance & Continuous Feedback Dashboard</h1>
        <p class=mt-1 text-sm text-slate-400>Monitor active performance cycle progression, OKR milestones, and review completion.</p>
    </div>

    <div class=grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4>
        <div class=rounded-xl border border-slate-800 bg-slate-900/50 p-5>
            <span class=text-sm font-medium text-slate-400>Active Cycle</span>
            <div class=mt-3 flex items-baseline gap-2>
                <span class=text-2xl font-bold text-white>2026 Annual</span>
                <span class=text-xs text-emerald-400 font-medium>In Progress</span>
            </div>
        </div>

        <div class=rounded-xl border border-slate-800 bg-slate-900/50 p-5>
            <span class=text-sm font-medium text-slate-400>Review Completion</span>
            <div class=mt-3 flex items-baseline gap-2>
                <span class=text-2xl font-bold text-white>78.4%</span>
                <span class=text-xs text-indigo-400 font-medium>Self & Manager</span>
            </div>
        </div>

        <div class=rounded-xl border border-slate-800 bg-slate-900/50 p-5>
            <span class=text-sm font-medium text-slate-400>Average Goal Attainment</span>
            <div class=mt-3 flex items-baseline gap-2>
                <span class=text-2xl font-bold text-white>84.2%</span>
                <span class=text-xs text-emerald-400 font-medium>On Track</span>
            </div>
        </div>

        <div class=rounded-xl border border-slate-800 bg-slate-900/50 p-5>
            <span class=text-sm font-medium text-slate-400>360° Feedback Requests</span>
            <div class=mt-3 flex items-baseline gap-2>
                <span class=text-2xl font-bold text-white>342</span>
                <span class=text-xs text-slate-400 font-medium>Anonymous Mode</span>
            </div>
        </div>
    </div>
</div>
@endsection
