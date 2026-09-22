@extends('benefits.layout')

@section('title', 'Open Enrollment Campaigns')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-white flex items-center gap-2">
                <i class="fa-solid fa-calendar-check text-emerald-400"></i> Open Enrollment Campaigns
            </h1>
            <p class="text-sm text-slate-400 mt-1">
                Manage annual enrollment windows, monitor eligible employee completion rates, and finalize election rosters.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="document.getElementById('new-enrollment-modal').classList.remove('hidden')" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-sm font-medium transition shadow-lg shadow-emerald-600/20 flex items-center gap-2">
                <i class="fa-solid fa-plus"></i> New Enrollment Window
            </button>
        </div>
    </div>

    <!-- Campaigns List -->
    <div class="space-y-4">
        @forelse($windows as $window)
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 hover:border-slate-700 transition">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-3">
                            <h2 class="text-xl font-bold text-white">{{ $window->name }}</h2>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase tracking-wider {{ $window->isOpen() ? 'bg-emerald-950 text-emerald-400 border border-emerald-800/60' : 'bg-slate-800 text-slate-400 border border-slate-700' }}">
                                {{ $window->status }}
                            </span>
                            <span class="text-xs font-mono text-slate-400">Plan Year {{ $window->plan_year }}</span>
                        </div>
                        <div class="flex flex-wrap items-center gap-4 text-xs text-slate-400 mt-2">
                            <span><i class="fa-regular fa-calendar mr-1"></i> Window: <strong>{{ $window->start_date->format('M d, Y') }}</strong> – <strong>{{ $window->close_date->format('M d, Y') }}</strong></span>
                            <span><i class="fa-solid fa-bolt mr-1 text-emerald-400"></i> Effective: <strong>{{ $window->effective_date->format('M d, Y') }}</strong></span>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <span class="text-xs text-slate-400">
                            {{ $window->elections->count() }} Submissions
                        </span>
                        <a href="{{ route('benefits.self_service.wizard') }}" class="px-3.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-lg text-xs font-medium border border-slate-700 transition flex items-center gap-1.5">
                            <i class="fa-solid fa-arrow-up-right-from-square"></i> Open Portal
                        </a>
                    </div>
                </div>

                <!-- Visual Progress Bar -->
                <div class="mt-5 pt-4 border-t border-slate-800/70">
                    <div class="flex items-center justify-between text-xs text-slate-300 mb-1.5 font-medium">
                        <span>Campaign Completion Progress</span>
                        <span class="font-mono text-emerald-400">78% Complete</span>
                    </div>
                    <div class="w-full h-2.5 bg-slate-800 rounded-full overflow-hidden flex">
                        <div class="bg-emerald-500 h-full" style="width: 65%" title="Completed Elections (65%)"></div>
                        <div class="bg-amber-500 h-full" style="width: 13%" title="Waived Benefits (13%)"></div>
                        <div class="bg-indigo-500 h-full" style="width: 10%" title="In Progress (10%)"></div>
                    </div>
                    <div class="flex items-center gap-4 text-[11px] text-slate-400 mt-2">
                        <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Completed</span>
                        <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-amber-500"></span> Waived</span>
                        <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-indigo-500"></span> In Progress</span>
                        <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-slate-700"></span> Not Started</span>
                    </div>
                </div>
            </div>
        @empty
            <div class="p-8 text-center bg-slate-900 border border-slate-800 rounded-xl">
                <i class="fa-solid fa-calendar-xmark text-slate-600 text-3xl mb-3"></i>
                <p class="text-slate-400 text-sm">No enrollment windows created yet.</p>
            </div>
        @endforelse
    </div>
</div>

<!-- New Enrollment Window Modal -->
<div id="new-enrollment-modal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-700/80 rounded-2xl w-full max-w-md overflow-hidden shadow-2xl p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
            <h3 class="text-lg font-bold text-white">Create Open Enrollment Window</h3>
            <button onclick="document.getElementById('new-enrollment-modal').classList.add('hidden')" class="text-slate-400 hover:text-white p-1">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <form onsubmit="handleCreateEnrollmentWindow(event)" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Campaign Name</label>
                <input type="text" required placeholder="Annual Benefits Open Enrollment 2027" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Plan Year</label>
                    <input type="number" required value="2027" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Effective Date</label>
                    <input type="date" required value="2027-01-01" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Window Open</label>
                    <input type="date" required value="2026-11-01" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Window Close</label>
                    <input type="date" required value="2026-11-30" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500">
                </div>
            </div>
            <div class="pt-2 border-t border-slate-800 flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('new-enrollment-modal').classList.add('hidden')" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold rounded-lg transition">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold rounded-lg shadow-lg shadow-emerald-600/30 transition">Launch Window</button>
            </div>
        </form>
    </div>
</div>

<script>
function handleCreateEnrollmentWindow(e) {
    e.preventDefault();
    document.getElementById('new-enrollment-modal').classList.add('hidden');
    window.showNotification('success', 'Open enrollment campaign configured and scheduled.');
}
</script>
@endsection
