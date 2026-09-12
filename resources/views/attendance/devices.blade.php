@extends('layouts.attendance')

@section('title', 'Attendance Devices & Terminals')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Attendance Devices & Connectors</h1>
            <p class="text-sm text-slate-400 mt-1">Manage biometric terminals, RFID gates, mobile clock-in geofences, and sync schedules.</p>
        </div>
        <button class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-sm transition flex items-center gap-2">
            <i class="fa-solid fa-plus"></i> Register Device
        </button>
    </div>

    <!-- Device List Table -->
    <div class="bg-slate-800/80 rounded-2xl border border-slate-700/60 overflow-hidden shadow-xl">
        <table class="w-full text-left text-sm text-slate-300">
            <thead class="bg-slate-900/50 text-xs uppercase text-slate-400 border-b border-slate-700/60">
                <tr>
                    <th class="px-6 py-4">Terminal</th>
                    <th class="px-6 py-4">Protocol / Vendor</th>
                    <th class="px-6 py-4">IP Address / Port</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4">Last Sync</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700/50">
                <tr class="hover:bg-slate-700/20">
                    <td class="px-6 py-4 font-semibold text-white">
                        <div>HQ Main Gate Terminal</div>
                        <span class="text-xs text-slate-400 font-mono">DEV-HQ-01</span>
                    </td>
                    <td class="px-6 py-4">ZKTeco Biometric</td>
                    <td class="px-6 py-4 font-mono text-xs">192.168.1.100 : 4370</td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Online
                        </span>
                    </td>
                    <td class="px-6 py-4 text-xs text-slate-400">Just now</td>
                    <td class="px-6 py-4 text-right space-x-2">
                        <button class="px-3 py-1.5 rounded-lg bg-indigo-600/30 text-indigo-300 text-xs font-semibold hover:bg-indigo-600/50 border border-indigo-500/30">
                            <i class="fa-solid fa-arrows-rotate mr-1"></i> Sync Now
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
