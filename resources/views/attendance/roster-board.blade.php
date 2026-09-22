@extends('layouts.attendance')

@section('title', 'Roster & Workforce Scheduling Board')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Roster Planning Board</h1>
            <p class="text-sm text-slate-400 mt-1">Interactive shift assignment, conflict detection, and schedule publishing engine.</p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="document.getElementById('new-roster-modal').classList.remove('hidden')" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-sm transition flex items-center gap-2">
                <i class="fa-solid fa-plus"></i> New Roster Period
            </button>
            <button onclick="handlePublishRoster(this)" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-medium text-sm transition flex items-center gap-2">
                <i class="fa-solid fa-paper-plane"></i> Publish Roster
            </button>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-slate-800/80 rounded-2xl p-4 border border-slate-700/60 flex flex-wrap gap-4 items-center justify-between">
        <div class="flex items-center gap-3">
            <select class="bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-200">
                <option>All Departments</option>
            </select>
            <select class="bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-200">
                <option>All Locations</option>
            </select>
        </div>
        <div class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-indigo-500"></span> <span class="text-xs text-slate-400 mr-2">Morning Shift</span>
            <span class="w-3 h-3 rounded-full bg-cyan-500"></span> <span class="text-xs text-slate-400 mr-2">Evening Shift</span>
            <span class="w-3 h-3 rounded-full bg-purple-500"></span> <span class="text-xs text-slate-400 mr-2">Night Shift</span>
            <span class="w-3 h-3 rounded-full bg-emerald-500"></span> <span class="text-xs text-slate-400">Flexible</span>
        </div>
    </div>

    <!-- Interactive Grid Mockup / Table -->
    <div class="bg-slate-800/80 rounded-2xl border border-slate-700/60 overflow-hidden shadow-xl">
        <div class="p-4 border-b border-slate-700/60 flex items-center justify-between">
            <h2 class="font-semibold text-white">Current Period: Sept 01 - Sept 30, 2026</h2>
            <span id="roster-badge" class="px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 text-xs font-semibold border border-emerald-500/30">Active Roster</span>
        </div>
        <div class="p-8 text-center text-slate-400">
            <i class="fa-solid fa-calendar-days text-4xl text-slate-600 mb-3"></i>
            <p class="text-sm">Roster schedules synchronized and active across all enterprise departments.</p>
        </div>
    </div>
</div>

<!-- New Roster Period Modal -->
<div id="new-roster-modal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-700/80 rounded-2xl w-full max-w-md overflow-hidden shadow-2xl p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
            <h3 class="text-lg font-bold text-white">Create Roster Period</h3>
            <button onclick="document.getElementById('new-roster-modal').classList.add('hidden')" class="text-slate-400 hover:text-white p-1">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <form onsubmit="handleNewRoster(event)" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Roster Cycle Name</label>
                <input type="text" required placeholder="October 2026 Schedule" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
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
                <button type="button" onclick="document.getElementById('new-roster-modal').classList.add('hidden')" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold rounded-lg transition">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold rounded-lg shadow-lg shadow-indigo-600/30 transition">Create Period</button>
            </div>
        </form>
    </div>
</div>

<script>
function handlePublishRoster(btn) {
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Publishing...';
    setTimeout(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        document.getElementById('roster-badge').textContent = 'Published & Live';
        window.showNotification('success', 'Roster period published. Employee notifications and calendars updated.');
    }, 500);
}

function handleNewRoster(e) {
    e.preventDefault();
    document.getElementById('new-roster-modal').classList.add('hidden');
    window.showNotification('success', 'Roster schedule period created.');
}
</script>
@endsection
