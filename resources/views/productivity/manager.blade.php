@extends('productivity.layout')

@section('title', 'Team Operational Productivity — Manager Portal')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-slate-200">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Team Operational Productivity & Efficiency</h1>
            <p class="text-sm text-slate-500 mt-1">Authorized scope: Operations Team A — Focus on process flow, capacity, and schedule coverage</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <span class="inline-flex items-center px-3 py-1 rounded-md text-xs font-semibold bg-emerald-100 text-emerald-800">
                <i class="fa-solid fa-lock mr-1.5"></i>Manager Scope Authorized
            </span>
        </div>
    </div>

    <!-- Manager KPIs -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Scheduled vs Attended</span>
            <div class="text-2xl font-bold text-slate-900 mt-2">1,600 / 1,580 <span class="text-xs font-normal text-slate-500">hrs</span></div>
            <div class="text-xs text-emerald-600 font-medium mt-1">98.7% Schedule Adherence</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Productive Time</span>
            <div class="text-2xl font-bold text-slate-900 mt-2">1,380 <span class="text-xs font-normal text-slate-500">hrs (87.3%)</span></div>
            <div class="text-xs text-slate-500 font-medium mt-1">200 hrs in training/meetings/buffer</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Team Output</span>
            <div class="text-2xl font-bold text-slate-900 mt-2">24,840 <span class="text-xs font-normal text-slate-500">cases</span></div>
            <div class="text-xs text-blue-600 font-medium mt-1">18.0 cases / productive hour</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Quality Rate</span>
            <div class="text-2xl font-bold text-slate-900 mt-2">98.2%</div>
            <div class="text-xs text-emerald-600 font-medium mt-1">First-pass completion rate</div>
        </div>
    </div>

    <!-- Schedule Effectiveness & Bottleneck Investigation -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <h2 class="text-base font-semibold text-slate-900 mb-4">Schedule Effectiveness & Hour Distribution</h2>
            <div class="space-y-4 text-sm">
                <div>
                    <div class="flex justify-between text-xs font-medium text-slate-700 mb-1">
                        <span>Direct Value-Add Productive Work</span>
                        <span>87.3% (1,380 hrs)</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2.5">
                        <div class="bg-emerald-500 h-2.5 rounded-full" style="width: 87.3%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-xs font-medium text-slate-700 mb-1">
                        <span>Training & Structured Development</span>
                        <span>6.3% (100 hrs)</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2.5">
                        <div class="bg-blue-500 h-2.5 rounded-full" style="width: 6.3%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-xs font-medium text-slate-700 mb-1">
                        <span>Team Meetings & Admin Operations</span>
                        <span>3.8% (60 hrs)</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2.5">
                        <div class="bg-purple-500 h-2.5 rounded-full" style="width: 3.8%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-xs font-medium text-slate-700 mb-1">
                        <span>Waiting Time / System Latency Buffer</span>
                        <span>2.6% (40 hrs)</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2.5">
                        <div class="bg-amber-500 h-2.5 rounded-full" style="width: 2.6%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-4">
            <h2 class="text-base font-semibold text-slate-900">Operational Bottleneck Analysis (Non-Punitive)</h2>
            <div class="p-4 rounded-lg bg-amber-50 border border-amber-200 text-sm text-amber-900 space-y-1">
                <div class="font-semibold flex items-center">
                    <i class="fa-solid fa-triangle-exclamation mr-2 text-amber-600"></i>
                    Shift Handoff Delay Detected
                </div>
                <p class="text-xs text-amber-800">
                    Average waiting time spikes by 18 minutes during the 14:00 shift transition. Investigate digital handover checklist rather than individual staffing adjustments.
                </p>
            </div>
            <div class="p-4 rounded-lg bg-blue-50 border border-blue-200 text-sm text-blue-900 space-y-1">
                <div class="font-semibold flex items-center">
                    <i class="fa-solid fa-clock-rotate-left mr-2 text-blue-600"></i>
                    Absence Coverage Redistribution
                </div>
                <p class="text-xs text-blue-800">
                    16 hours of unplanned sick leave covered via planned flex capacity with 0 overtime cost incurred.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
