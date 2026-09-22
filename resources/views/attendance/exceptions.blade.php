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
                        <button onclick="document.getElementById('resolve-exception-modal').classList.remove('hidden')" class="px-3 py-1.5 rounded-lg bg-indigo-600/30 text-indigo-300 text-xs font-semibold hover:bg-indigo-600/50 border border-indigo-500/30 transition">
                            Resolve
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Resolve Exception Modal -->
<div id="resolve-exception-modal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-700/80 rounded-2xl w-full max-w-md overflow-hidden shadow-2xl p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
            <div>
                <h3 class="text-lg font-bold text-white">Resolve Attendance Exception</h3>
                <p class="text-xs text-slate-400">Shahid Khan &bull; Missing Clock-Out (2026-09-02)</p>
            </div>
            <button onclick="document.getElementById('resolve-exception-modal').classList.add('hidden')" class="text-slate-400 hover:text-white p-1">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <form onsubmit="handleResolveSubmit(event)" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Resolution Action</label>
                <select class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                    <option value="insert">Insert Standard Out-Punch (17:00:00)</option>
                    <option value="excuse">Excuse / Waive Exception with Manager Approval</option>
                    <option value="mark_half_day">Mark as Half Day Absent</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Audit Justification</label>
                <textarea required rows="2" placeholder="Verified manager confirmation..." class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500"></textarea>
            </div>
            <div class="pt-2 border-t border-slate-800 flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('resolve-exception-modal').classList.add('hidden')" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold rounded-lg transition">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold rounded-lg transition">Apply Resolution</button>
            </div>
        </form>
    </div>
</div>

<script>
function handleResolveSubmit(e) {
    e.preventDefault();
    document.getElementById('resolve-exception-modal').classList.add('hidden');
    window.showNotification('success', 'Attendance exception resolved and recalculation triggered.');
}
</script>
@endsection
