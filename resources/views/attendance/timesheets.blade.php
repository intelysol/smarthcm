@extends('layouts.attendance')

@section('title', 'Timesheet Management')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Employee Timesheets & Approvals</h1>
            <p class="text-sm text-slate-400 mt-1">Aggregated pay-period timesheet records, manager reviews, and payroll locking.</p>
        </div>
        <button onclick="handleGenerateBatchTimesheets(this)" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-sm transition flex items-center gap-2">
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
                        <button onclick="document.getElementById('timesheet-details-modal').classList.remove('hidden')" class="px-3 py-1.5 rounded-lg bg-slate-700 text-slate-200 text-xs font-semibold hover:bg-slate-600 transition">
                            View Details
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Timesheet Details Modal -->
<div id="timesheet-details-modal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-700/80 rounded-2xl w-full max-w-lg overflow-hidden shadow-2xl p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
            <div>
                <h3 class="text-lg font-bold text-white">Timesheet Breakdown</h3>
                <p class="text-xs text-slate-400">Shahid Khan &bull; Sept 01 - Sept 30, 2026</p>
            </div>
            <button onclick="document.getElementById('timesheet-details-modal').classList.add('hidden')" class="text-slate-400 hover:text-white p-1">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <div class="grid grid-cols-2 gap-4 text-xs">
            <div class="bg-slate-800/60 p-3 rounded-xl border border-slate-700/50">
                <span class="text-slate-400">Regular Work Hours</span>
                <div class="text-lg font-bold text-white mt-1">160.00 hrs</div>
            </div>
            <div class="bg-slate-800/60 p-3 rounded-xl border border-slate-700/50">
                <span class="text-slate-400">Approved Overtime</span>
                <div class="text-lg font-bold text-indigo-400 mt-1">8.00 hrs</div>
            </div>
            <div class="bg-slate-800/60 p-3 rounded-xl border border-slate-700/50">
                <span class="text-slate-400">Rest Day Work</span>
                <div class="text-lg font-bold text-emerald-400 mt-1">0.00 hrs</div>
            </div>
            <div class="bg-slate-800/60 p-3 rounded-xl border border-slate-700/50">
                <span class="text-slate-400">Paid Leave Deductions</span>
                <div class="text-lg font-bold text-amber-400 mt-1">0.00 hrs</div>
            </div>
        </div>
        <div class="p-3 bg-slate-800/40 rounded-xl border border-slate-700/30 text-xs text-slate-300 space-y-1">
            <div class="flex justify-between"><span>Audit Status:</span><span class="text-emerald-400 font-semibold">Verified & Locked</span></div>
            <div class="flex justify-between"><span>Payroll Ingest:</span><span class="text-indigo-400 font-semibold">Ready for Batch Run</span></div>
        </div>
        <div class="pt-2 border-t border-slate-800 flex justify-end">
            <button onclick="document.getElementById('timesheet-details-modal').classList.add('hidden')" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold rounded-lg transition">Close</button>
        </div>
    </div>
</div>

<script>
function handleGenerateBatchTimesheets(btn) {
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Calculating...';
    setTimeout(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        window.showNotification('success', 'Batch timesheet calculation completed for all active employees.');
    }, 600);
}
</script>
@endsection
