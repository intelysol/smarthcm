@extends('layouts.attendance')

@section('title', 'Shift & Pattern Configuration')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Shift Definitions & Rotation Patterns</h1>
            <p class="text-sm text-slate-400 mt-1">Configure standard, overnight, flexible, and split shifts with tolerance windows and breaks.</p>
        </div>
        <button onclick="document.getElementById('create-shift-modal').classList.remove('hidden')" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-sm transition flex items-center gap-2">
            <i class="fa-solid fa-plus"></i> Create Shift
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        <!-- Morning Shift Card -->
        <div class="bg-slate-800/80 rounded-2xl p-5 border border-slate-700/60 shadow-xl space-y-4">
            <div class="flex items-center justify-between">
                <span class="px-2.5 py-1 rounded-lg bg-indigo-500/20 text-indigo-300 font-semibold text-xs border border-indigo-500/30">SHIFT-MORN</span>
                <span class="text-xs text-slate-400 font-mono">08:00 &mdash; 17:00</span>
            </div>
            <div>
                <h2 class="text-lg font-bold text-white">Standard Morning Shift</h2>
                <p class="text-xs text-slate-400 mt-1">8 hours standard duty + 60m lunch break. 15m grace period.</p>
            </div>
            <div class="pt-3 border-t border-slate-700/60 flex items-center justify-between text-xs text-slate-400">
                <span>Grace: 15 mins</span>
                <span class="text-emerald-400 font-medium">Overtime Eligible</span>
            </div>
        </div>

        <!-- Night Shift Card -->
        <div class="bg-slate-800/80 rounded-2xl p-5 border border-slate-700/60 shadow-xl space-y-4">
            <div class="flex items-center justify-between">
                <span class="px-2.5 py-1 rounded-lg bg-purple-500/20 text-purple-300 font-semibold text-xs border border-purple-500/30">SHIFT-NIGHT</span>
                <span class="text-xs text-slate-400 font-mono">22:00 &mdash; 06:00 (+1)</span>
            </div>
            <div>
                <h2 class="text-lg font-bold text-white">Overnight Shift</h2>
                <p class="text-xs text-slate-400 mt-1">Cross-midnight nocturnal rotation with overnight window pairing.</p>
            </div>
            <div class="pt-3 border-t border-slate-700/60 flex items-center justify-between text-xs text-slate-400">
                <span>Overnight: Yes</span>
                <span class="text-purple-400 font-medium">Night Differential</span>
            </div>
        </div>

        <!-- Flexible Shift Card -->
        <div class="bg-slate-800/80 rounded-2xl p-5 border border-slate-700/60 shadow-xl space-y-4">
            <div class="flex items-center justify-between">
                <span class="px-2.5 py-1 rounded-lg bg-emerald-500/20 text-emerald-300 font-semibold text-xs border border-emerald-500/30">SHIFT-FLEX</span>
                <span class="text-xs text-slate-400 font-mono">07:00 &mdash; 19:00</span>
            </div>
            <div>
                <h2 class="text-lg font-bold text-white">Flexible Hours Shift</h2>
                <p class="text-xs text-slate-400 mt-1">Core hours presence 10:00 &mdash; 15:00. 480 required daily minutes.</p>
            </div>
            <div class="pt-3 border-t border-slate-700/60 flex items-center justify-between text-xs text-slate-400">
                <span>Core: 10:00 - 15:00</span>
                <span class="text-emerald-400 font-medium">Auto Balanced</span>
            </div>
        </div>
    </div>
</div>

<!-- Create Shift Modal -->
<div id="create-shift-modal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-700/80 rounded-2xl w-full max-w-md overflow-hidden shadow-2xl p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
            <h3 class="text-lg font-bold text-white">Define New Shift</h3>
            <button onclick="document.getElementById('create-shift-modal').classList.add('hidden')" class="text-slate-400 hover:text-white p-1">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <form onsubmit="handleCreateShift(event)" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Shift Name</label>
                <input type="text" required placeholder="e.g. Afternoon Support Shift" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Start Time</label>
                    <input type="time" required value="14:00" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">End Time</label>
                    <input type="time" required value="22:00" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Grace Period (Mins)</label>
                    <input type="number" value="15" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Break Duration (Mins)</label>
                    <input type="number" value="60" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                </div>
            </div>
            <div class="pt-2 border-t border-slate-800 flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('create-shift-modal').classList.add('hidden')" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold rounded-lg transition">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold rounded-lg shadow-lg shadow-indigo-600/30 transition">Save Shift</button>
            </div>
        </form>
    </div>
</div>

<script>
function handleCreateShift(e) {
    e.preventDefault();
    document.getElementById('create-shift-modal').classList.add('hidden');
    window.showNotification('success', 'Shift definition created and added to roster options.');
}
</script>
@endsection
