@extends('health_safety.layout')

@section('title', 'Safety Incident Management')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-white">Occupational Safety Incidents</h1>
            <p class="mt-1 text-sm text-slate-400">Log near-misses, injuries, equipment hazards, and OSHA recordable incidents.</p>
        </div>
        <div>
            <button type="button" class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 transition">
                + Report New Incident
            </button>
        </div>
    </div>

    <!-- Incident Filter Bar -->
    <div class="rounded-xl border border-slate-800 bg-slate-950/40 p-4">
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-medium text-slate-400">Severity</label>
                <select class="mt-1 block w-full rounded-md border-0 bg-slate-900 py-1.5 px-3 text-slate-200 ring-1 ring-inset ring-slate-800 focus:ring-2 focus:ring-emerald-500 text-sm">
                    <option value="">All Severities</option>
                    <option value="minor">Minor</option>
                    <option value="moderate">Moderate</option>
                    <option value="severe">Severe</option>
                    <option value="critical">Critical</option>
                    <option value="fatal">Fatal</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-400">Status</label>
                <select class="mt-1 block w-full rounded-md border-0 bg-slate-900 py-1.5 px-3 text-slate-200 ring-1 ring-inset ring-slate-800 focus:ring-2 focus:ring-emerald-500 text-sm">
                    <option value="">All Statuses</option>
                    <option value="reported">Reported</option>
                    <option value="under_investigation">Under Investigation</option>
                    <option value="action_pending">Action Pending</option>
                    <option value="closed">Closed</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-400">Type</label>
                <select class="mt-1 block w-full rounded-md border-0 bg-slate-900 py-1.5 px-3 text-slate-200 ring-1 ring-inset ring-slate-800 focus:ring-2 focus:ring-emerald-500 text-sm">
                    <option value="">All Types</option>
                    <option value="injury">Injury / Illness</option>
                    <option value="near_miss">Near Miss</option>
                    <option value="hazard_unsafe_condition">Hazard / Unsafe Condition</option>
                    <option value="environmental_spill">Environmental Spill</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="button" class="w-full rounded-md bg-slate-800 py-2 px-3 text-sm font-semibold text-slate-200 hover:bg-slate-700 transition">
                    Filter Incidents
                </button>
            </div>
        </div>
    </div>

    <!-- Incidents Table -->
    <div class="rounded-xl border border-slate-800 bg-slate-950/40 shadow-sm overflow-hidden">
        <table class="w-full text-left text-sm text-slate-300">
            <thead class="text-xs uppercase bg-slate-900/60 text-slate-400 border-b border-slate-800">
                <tr>
                    <th class="py-3 px-4">Incident #</th>
                    <th class="py-3 px-4">Date & Time</th>
                    <th class="py-3 px-4">Severity</th>
                    <th class="py-3 px-4">Type</th>
                    <th class="py-3 px-4">Title & Details</th>
                    <th class="py-3 px-4">OSHA</th>
                    <th class="py-3 px-4">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
                <tr class="hover:bg-slate-900/40 transition">
                    <td class="py-3 px-4 font-mono text-xs text-emerald-400">INC-20260904-0001</td>
                    <td class="py-3 px-4 text-xs">2026-09-04 09:15</td>
                    <td class="py-3 px-4">
                        <span class="inline-flex rounded-full bg-rose-500/10 px-2 py-0.5 text-xs font-medium text-rose-400 ring-1 ring-inset ring-rose-500/20">
                            Severe
                        </span>
                    </td>
                    <td class="py-3 px-4 text-xs">Injury</td>
                    <td class="py-3 px-4">
                        <div class="font-medium text-white">Forklift Impact Pinch Hazard</div>
                        <div class="text-xs text-slate-400">Loading Dock 4, Distribution Center</div>
                    </td>
                    <td class="py-3 px-4">
                        <span class="inline-flex rounded bg-amber-500/10 px-1.5 py-0.5 text-[10px] font-medium text-amber-400">Recordable</span>
                    </td>
                    <td class="py-3 px-4">
                        <span class="inline-flex rounded-full bg-blue-500/10 px-2 py-0.5 text-xs font-medium text-blue-400 ring-1 ring-inset ring-blue-500/20">
                            Under Investigation
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
