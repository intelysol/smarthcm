@extends('layouts.attendance')

@section('title', 'Timesheet Management')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Employee Timesheets & Approvals</h1>
            <p class="text-sm text-slate-400 mt-1">Aggregated pay-period timesheet records, manager reviews, and payroll locking.</p>
        </div>
        <button class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-sm transition flex items-center gap-2">
            <i class="fa-solid fa-calculator"></i> Generate Batch Timesheets
        </button>
    </div>

    <!-- Timesheets List -->
    <div class="bg-slate-800/80 rounded-2xl border border-slate-700/60 overflow-hidden shadow-xl">
        <table class="w-full text-left text-sm text-slate-300">
            <thead class="bg-slate-900/50 text-xs uppercase text-slate-400 border-b border-slate-700/60">
                <tr>
                    <th class="px-6 py-4">Employee</th>
                    <th class="px-6 py-4">Period</th>
                    <th class="px-6 py-4">Scheduled</th>
                    <th class="px-6 py-4">Worked</th>
                    <th class="px-6 py-4">Approved OT</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700/50">
                <tr class="hover:bg-slate-700/20">
                    <td class="px-6 py-4 font-semibold text-white">Shahid Khan</td>
                    <td class="px-6 py-4 text-xs font-mono">2026-09-01 &mdash; 2026-09-30</td>
                    <td class="px-6 py-4">160 hrs</td>
                    <td class="px-6 py-4 text-emerald-400 font-semibold">168 hrs</td>
                    <td class="px-6 py-4 text-indigo-400">8 hrs</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-0.5 rounded text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">HR Approved</span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <button class="px-3 py-1.5 rounded-lg bg-slate-700 text-slate-200 text-xs font-semibold hover:bg-slate-600">
                            View Details
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
