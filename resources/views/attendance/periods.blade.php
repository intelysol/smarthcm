@extends('layouts.attendance')

@section('title', 'Attendance Period Cutoff & Locking')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Attendance Periods & Cutoff Locking</h1>
            <p class="text-sm text-slate-400 mt-1">Manage payroll cutoff locking, prevent retroactive attendance tampering, and perform audited reopenings.</p>
        </div>
        <button class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-sm transition flex items-center gap-2">
            <i class="fa-solid fa-plus"></i> Create Attendance Period
        </button>
    </div>

    <!-- Periods List -->
    <div class="bg-slate-800/80 rounded-2xl border border-slate-700/60 overflow-hidden shadow-xl">
        <table class="w-full text-left text-sm text-slate-300">
            <thead class="bg-slate-900/50 text-xs uppercase text-slate-400 border-b border-slate-700/60">
                <tr>
                    <th class="px-6 py-4">Period Name</th>
                    <th class="px-6 py-4">Date Range</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4">Locked By / At</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700/50">
                <tr class="hover:bg-slate-700/20">
                    <td class="px-6 py-4 font-semibold text-white">September 2026 Monthly Attendance</td>
                    <td class="px-6 py-4 text-xs font-mono">2026-09-01 &mdash; 2026-09-30</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-0.5 rounded text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Open</span>
                    </td>
                    <td class="px-6 py-4 text-xs text-slate-400">&mdash;</td>
                    <td class="px-6 py-4 text-right">
                        <button class="px-3 py-1.5 rounded-lg bg-rose-600/30 text-rose-300 text-xs font-semibold hover:bg-rose-600/50 border border-rose-500/30">
                            <i class="fa-solid fa-lock mr-1"></i> Lock Period
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
