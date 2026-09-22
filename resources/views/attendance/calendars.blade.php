@extends('layouts.attendance')

@section('title', 'Work & Holiday Calendars')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Work Calendars & Holiday Schedules</h1>
            <p class="text-sm text-slate-400 mt-1">Multi-entity weekly working day patterns and annual holiday schedules.</p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="document.getElementById('work-calendar-modal').classList.remove('hidden')" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-sm transition flex items-center gap-2">
                <i class="fa-solid fa-plus"></i> New Work Calendar
            </button>
            <button onclick="document.getElementById('holiday-calendar-modal').classList.remove('hidden')" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-medium text-sm transition flex items-center gap-2">
                <i class="fa-solid fa-calendar-plus"></i> Add Holiday Calendar
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Work Calendar -->
        <div class="bg-slate-800/80 rounded-2xl p-6 border border-slate-700/60 shadow-xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-700/60 pb-3">
                <div>
                    <h2 class="font-bold text-white">Standard 5-Day Work Calendar</h2>
                    <span class="text-xs text-slate-400">STD-5DAY (UTC)</span>
                </div>
                <span class="px-2.5 py-1 rounded-full bg-emerald-500/20 text-emerald-300 text-xs font-semibold">Default</span>
            </div>
            <div class="space-y-2">
                <div class="flex justify-between text-xs py-1 border-b border-slate-700/40 text-slate-300">
                    <span>Monday &mdash; Friday</span>
                    <span class="text-emerald-400 font-medium">Working Day (8 hrs)</span>
                </div>
                <div class="flex justify-between text-xs py-1 text-slate-400">
                    <span>Saturday &mdash; Sunday</span>
                    <span class="text-rose-400 font-medium">Weekly Rest Days</span>
                </div>
            </div>
        </div>

        <!-- Holiday Calendar -->
        <div class="bg-slate-800/80 rounded-2xl p-6 border border-slate-700/60 shadow-xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-700/60 pb-3">
                <div>
                    <h2 class="font-bold text-white">Corporate Holidays 2026</h2>
                    <span class="text-xs text-slate-400">HOL-2026 (Annual)</span>
                </div>
                <span class="px-2.5 py-1 rounded-full bg-indigo-500/20 text-indigo-300 text-xs font-semibold">Active</span>
            </div>
            <div class="space-y-2 text-xs text-slate-300">
                <div class="flex justify-between py-1 border-b border-slate-700/40">
                    <span>2026-01-01 &mdash; New Year's Day</span>
                    <span class="text-indigo-400">Public</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-700/40">
                    <span>2026-05-01 &mdash; Labor Day</span>
                    <span class="text-indigo-400">Public</span>
                </div>
                <div class="flex justify-between py-1">
                    <span>2026-08-14 &mdash; Independence Day</span>
                    <span class="text-indigo-400">National</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Work Calendar Modal -->
<div id="work-calendar-modal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-700/80 rounded-2xl w-full max-w-md overflow-hidden shadow-2xl p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
            <h3 class="text-lg font-bold text-white">Create Work Calendar</h3>
            <button onclick="document.getElementById('work-calendar-modal').classList.add('hidden')" class="text-slate-400 hover:text-white p-1">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <form onsubmit="handleCalendarSubmit(event, 'Work Calendar created successfully.', 'work-calendar-modal')" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Calendar Name</label>
                <input type="text" required placeholder="e.g. 6-Day Retail Shift Pattern" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Working Days</label>
                <select class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                    <option value="5">Monday – Friday (5 Days / 40 hrs)</option>
                    <option value="6">Monday – Saturday (6 Days / 48 hrs)</option>
                    <option value="4">4-Day Compressed (36 hrs)</option>
                </select>
            </div>
            <div class="pt-2 border-t border-slate-800 flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('work-calendar-modal').classList.add('hidden')" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold rounded-lg transition">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold rounded-lg shadow-lg shadow-indigo-600/30 transition">Save Calendar</button>
            </div>
        </form>
    </div>
</div>

<!-- Holiday Calendar Modal -->
<div id="holiday-calendar-modal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-700/80 rounded-2xl w-full max-w-md overflow-hidden shadow-2xl p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
            <h3 class="text-lg font-bold text-white">Add Holiday Schedule</h3>
            <button onclick="document.getElementById('holiday-calendar-modal').classList.add('hidden')" class="text-slate-400 hover:text-white p-1">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <form onsubmit="handleCalendarSubmit(event, 'Holiday calendar created successfully.', 'holiday-calendar-modal')" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Calendar Name</label>
                <input type="text" required placeholder="e.g. Public Holidays 2027" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Applicable Year</label>
                <input type="number" value="2026" required class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
            </div>
            <div class="pt-2 border-t border-slate-800 flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('holiday-calendar-modal').classList.add('hidden')" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold rounded-lg transition">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white text-xs font-semibold rounded-lg transition">Create Schedule</button>
            </div>
        </form>
    </div>
</div>

<script>
function handleCalendarSubmit(e, message, modalId) {
    e.preventDefault();
    document.getElementById(modalId).classList.add('hidden');
    window.showNotification('success', message);
}
</script>
@endsection
