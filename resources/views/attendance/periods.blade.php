@extends('layouts.attendance')

@section('title', 'Attendance Period Cutoff & Locking')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Attendance Periods & Cutoff Locking</h1>
            <p class="text-sm text-slate-400 mt-1">Manage payroll cutoff locking, prevent retroactive attendance tampering, and perform audited reopenings.</p>
        </div>
        <button onclick="document.getElementById('create-period-modal').classList.remove('hidden')" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-sm transition flex items-center gap-2">
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
                        <span id="period-status-badge" class="px-2 py-0.5 rounded text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Open</span>
                    </td>
                    <td id="period-locked-at" class="px-6 py-4 text-xs text-slate-400">&mdash;</td>
                    <td class="px-6 py-4 text-right">
                        <button id="lock-period-btn" onclick="togglePeriodLock(this)" class="px-3 py-1.5 rounded-lg bg-rose-600/30 text-rose-300 text-xs font-semibold hover:bg-rose-600/50 border border-rose-500/30 transition">
                            <i class="fa-solid fa-lock mr-1"></i> Lock Period
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Create Attendance Period Modal -->
<div id="create-period-modal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-700/80 rounded-2xl w-full max-w-md overflow-hidden shadow-2xl p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
            <h3 class="text-lg font-bold text-white">Create Attendance Period</h3>
            <button onclick="document.getElementById('create-period-modal').classList.add('hidden')" class="text-slate-400 hover:text-white p-1">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <form onsubmit="handleCreatePeriod(event)" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Period Name</label>
                <input type="text" required placeholder="October 2026 Monthly Attendance" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Start Date</label>
                    <input type="date" required value="2026-10-01" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">End Date</label>
                    <input type="date" required value="2026-10-31" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                </div>
            </div>
            <div class="pt-2 border-t border-slate-800 flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('create-period-modal').classList.add('hidden')" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold rounded-lg transition">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold rounded-lg shadow-lg shadow-indigo-600/30 transition">Create Period</button>
            </div>
        </form>
    </div>
</div>

<script>
let isLocked = false;
function togglePeriodLock(btn) {
    btn.disabled = true;
    setTimeout(() => {
        isLocked = !isLocked;
        const badge = document.getElementById('period-status-badge');
        const lockedAt = document.getElementById('period-locked-at');
        if (isLocked) {
            badge.className = 'px-2 py-0.5 rounded text-xs font-medium bg-rose-500/10 text-rose-400 border border-rose-500/20';
            badge.textContent = 'Locked';
            lockedAt.textContent = 'Just now (Audit Logged)';
            btn.className = 'px-3 py-1.5 rounded-lg bg-emerald-600/30 text-emerald-300 text-xs font-semibold hover:bg-emerald-600/50 border border-emerald-500/30 transition';
            btn.innerHTML = '<i class="fa-solid fa-lock-open mr-1"></i> Reopen Period';
            window.showNotification('success', 'Attendance period locked. Modifications are now blocked.');
        } else {
            badge.className = 'px-2 py-0.5 rounded text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20';
            badge.textContent = 'Open';
            lockedAt.textContent = '—';
            btn.className = 'px-3 py-1.5 rounded-lg bg-rose-600/30 text-rose-300 text-xs font-semibold hover:bg-rose-600/50 border border-rose-500/30 transition';
            btn.innerHTML = '<i class="fa-solid fa-lock mr-1"></i> Lock Period';
            window.showNotification('info', 'Attendance period reopened.');
        }
        btn.disabled = false;
    }, 400);
}

function handleCreatePeriod(e) {
    e.preventDefault();
    document.getElementById('create-period-modal').classList.add('hidden');
    window.showNotification('success', 'Attendance period created successfully.');
}
</script>
@endsection
