@extends('layouts.attendance')

@section('title', 'Attendance Exceptions Queue')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Attendance Exceptions Resolution Queue</h1>
            <p class="text-sm text-slate-400 mt-1">Review and resolve missing punches, tardiness thresholds, and unscheduled attendance alerts.</p>
        </div>
    </div>

    <!-- Exceptions List -->
    <div class="bg-slate-800/80 rounded-2xl border border-slate-700/60 overflow-hidden shadow-xl">
        <table class="w-full text-left text-sm text-slate-300">
            <thead class="bg-slate-900/50 text-xs uppercase text-slate-400 border-b border-slate-700/60">
                <tr>
                    <th class="px-6 py-4">Employee</th>
                    <th class="px-6 py-4">Date</th>
                    <th class="px-6 py-4">Exception Type</th>
                    <th class="px-6 py-4">Severity</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700/50">
                <tr class="hover:bg-slate-700/20">
                    <td class="px-6 py-4 font-semibold text-white">Shahid Khan (EMP-1001)</td>
                    <td class="px-6 py-4 text-xs font-mono">2026-09-02</td>
                    <td class="px-6 py-4">
                        <span class="font-medium text-rose-400">Missing Clock-Out</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-0.5 rounded text-xs font-medium bg-rose-500/10 text-rose-400 border border-rose-500/20">Critical</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-0.5 rounded text-xs font-medium bg-amber-500/10 text-amber-400 border border-amber-500/20">Open</span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <button class="px-3 py-1.5 rounded-lg bg-indigo-600/30 text-indigo-300 text-xs font-semibold hover:bg-indigo-600/50 border border-indigo-500/30">
                            Resolve
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
