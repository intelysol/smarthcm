@extends('health_safety.layout')

@section('title', 'Return to Work (RTW) & Disability Coordination')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-white">Return to Work Coordination</h1>
            <p class="mt-1 text-sm text-slate-400">Manage phased employee transitions following occupational illness or injury leave.</p>
        </div>
        <div>
            <button type="button" onclick="document.getElementById('initiate-rtw-modal').classList.remove('hidden')" class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 transition">
                + Initiate RTW Case
            </button>
        </div>
    </div>

    <!-- Active Cases Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="rounded-xl border border-slate-800 bg-slate-950/40 p-5 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <span class="text-xs font-mono text-emerald-400">RTW-202609-0012</span>
                <span class="inline-flex rounded-full bg-purple-500/10 px-2 py-0.5 text-xs font-medium text-purple-400 ring-1 ring-inset ring-purple-500/20">
                    Phased Return
                </span>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-white">Production Line Technician</h3>
                <p class="text-xs text-slate-400">Manufacturing Plant 2</p>
            </div>
            <div class="rounded-lg bg-slate-900/60 p-3 text-xs space-y-1.5 border border-slate-800">
                <div class="flex justify-between text-slate-400">
                    <span>Target Return:</span>
                    <span class="font-medium text-slate-200">2026-09-20</span>
                </div>
                <div class="flex justify-between text-slate-400">
                    <span>Current Phase:</span>
                    <span class="font-medium text-emerald-400">Phase 2 (24 hrs/wk)</span>
                </div>
                <div class="flex justify-between text-slate-400">
                    <span>Accommodation:</span>
                    <span class="font-medium text-slate-200">Anti-fatigue station</span>
                </div>
            </div>
            <button type="button" onclick="document.getElementById('rtw-progression-modal').classList.remove('hidden')" class="w-full rounded-md bg-slate-800 py-1.5 px-3 text-xs font-semibold text-slate-200 hover:bg-slate-700 transition">
                View Progression
            </button>
        </div>
    </div>
</div>

<!-- Initiate RTW Case Modal -->
<div id="initiate-rtw-modal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-700/80 rounded-2xl w-full max-w-md overflow-hidden shadow-2xl p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
            <h3 class="text-lg font-bold text-white">Initiate Return to Work Case</h3>
            <button onclick="document.getElementById('initiate-rtw-modal').classList.add('hidden')" class="text-slate-400 hover:text-white p-1">&times;</button>
        </div>
        <form onsubmit="handleInitiateRtw(event)" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Employee Name / ID</label>
                <input type="text" required placeholder="EMP-2026-0042" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Return Model</label>
                <select class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500">
                    <option value="phased">Phased Hours Escalation (16 &rarr; 24 &rarr; 40 hrs)</option>
                    <option value="modified_duty">Light / Modified Duty Accommodation</option>
                    <option value="remote">Temporary Remote Work Authorization</option>
                    <option value="full">Full Clearance Immediate Reinstatement</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Target Return Date</label>
                <input type="date" required value="{{ now()->addDays(14)->toDateString() }}" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500">
            </div>
            <div class="pt-2 border-t border-slate-800 flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('initiate-rtw-modal').classList.add('hidden')" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold rounded-lg transition">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold rounded-lg shadow-lg shadow-emerald-600/30 transition">Initiate Case</button>
            </div>
        </form>
    </div>
</div>

<!-- RTW Progression Modal -->
<div id="rtw-progression-modal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-700/80 rounded-2xl w-full max-w-md overflow-hidden shadow-2xl p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
            <div>
                <h3 class="text-lg font-bold text-white">RTW Progression Timeline</h3>
                <p class="text-xs text-slate-400">Case RTW-202609-0012 &bull; Phased Return</p>
            </div>
            <button onclick="document.getElementById('rtw-progression-modal').classList.add('hidden')" class="text-slate-400 hover:text-white p-1">&times;</button>
        </div>
        <div class="space-y-3 text-xs">
            <div class="p-3 bg-emerald-500/10 border border-emerald-500/20 rounded-xl">
                <span class="font-bold text-emerald-400">Phase 1: 16 hrs/week (Completed)</span>
                <p class="text-slate-300 mt-1">Ergonomic workstation evaluation cleared by PT.</p>
            </div>
            <div class="p-3 bg-indigo-500/10 border border-indigo-500/20 rounded-xl">
                <span class="font-bold text-indigo-400">Phase 2: 24 hrs/week (Active)</span>
                <p class="text-slate-300 mt-1">Currently working 3 days/week with anti-fatigue matting.</p>
            </div>
            <div class="p-3 bg-slate-800/60 border border-slate-700/50 rounded-xl text-slate-400">
                <span class="font-bold text-slate-300">Phase 3: 40 hrs/week (Scheduled Sept 20)</span>
                <p class="mt-1">Pending final medical clearance and supervisor signoff.</p>
            </div>
        </div>
        <div class="pt-2 border-t border-slate-800 flex justify-end">
            <button onclick="document.getElementById('rtw-progression-modal').classList.add('hidden')" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold rounded-lg transition">Close</button>
        </div>
    </div>
</div>

<script>
function handleInitiateRtw(e) {
    e.preventDefault();
    document.getElementById('initiate-rtw-modal').classList.add('hidden');
    window.showNotification('success', 'Return to Work case initiated. Occupational health and supervisor notified.');
}
</script>
@endsection
