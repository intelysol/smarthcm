@extends('benefits.layout')

@section('title', 'Benefit Programs')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-white flex items-center gap-2">
                <i class="fa-solid fa-layer-group text-emerald-400"></i> Benefit Programs
            </h1>
            <p class="text-sm text-slate-400 mt-1">
                Configure organizational benefit umbrella programs (Health & Medical, Protection, Retirement, Wellness).
            </p>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="document.getElementById('new-program-modal').classList.remove('hidden')" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-sm font-medium transition shadow-lg shadow-emerald-600/20 flex items-center gap-2">
                <i class="fa-solid fa-plus"></i> New Program
            </button>
        </div>
    </div>

    <!-- Programs Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($programs as $program)
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 hover:border-slate-700 transition flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase tracking-wider bg-emerald-950 text-emerald-400 border border-emerald-800/50">
                            {{ str_replace('_', ' ', $program->category) }}
                        </span>
                        <span class="text-xs font-mono text-slate-500">v{{ $program->versions->count() ?: 1 }}</span>
                    </div>
                    <h3 class="text-lg font-bold text-white">{{ $program->name }}</h3>
                    <p class="text-xs font-mono text-slate-400 mt-0.5">{{ $program->code }}</p>
                    <p class="text-sm text-slate-300 mt-3 line-clamp-2">{{ $program->description ?? 'Comprehensive benefit package offering multi-tier coverage for employees.' }}</p>
                </div>

                <div class="mt-6 pt-4 border-t border-slate-800/80">
                    <div class="flex items-center justify-between text-xs text-slate-400">
                        <span><i class="fa-solid fa-shield-halved text-emerald-400 mr-1"></i> {{ $program->plans->count() }} Linked Plans</span>
                        <span class="inline-flex items-center gap-1.5 text-emerald-400">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span> Active
                        </span>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full p-8 text-center bg-slate-900 border border-slate-800 rounded-xl">
                <i class="fa-solid fa-layer-group text-slate-600 text-3xl mb-3"></i>
                <p class="text-slate-400 text-sm">No benefit programs configured yet.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $programs->links() }}
    </div>
</div>

<!-- New Program Modal -->
<div id="new-program-modal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-700/80 rounded-2xl w-full max-w-md overflow-hidden shadow-2xl p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
            <h3 class="text-lg font-bold text-white">Create Benefit Program</h3>
            <button onclick="document.getElementById('new-program-modal').classList.add('hidden')" class="text-slate-400 hover:text-white p-1">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <form onsubmit="handleCreateProgram(event)" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Program Name</label>
                <input type="text" required placeholder="Comprehensive Health & Medical" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Program Code</label>
                    <input type="text" required placeholder="PRG-HEALTH-01" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Category</label>
                    <select class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500">
                        <option value="health">Health & Medical</option>
                        <option value="protection">Life & Protection</option>
                        <option value="retirement">Retirement & 401(k)</option>
                        <option value="wellness">Wellness & Perks</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Description</label>
                <textarea rows="2" placeholder="Program overview and objectives..." class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500"></textarea>
            </div>
            <div class="pt-2 border-t border-slate-800 flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('new-program-modal').classList.add('hidden')" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold rounded-lg transition">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold rounded-lg shadow-lg shadow-emerald-600/30 transition">Save Program</button>
            </div>
        </form>
    </div>
</div>

<script>
function handleCreateProgram(e) {
    e.preventDefault();
    document.getElementById('new-program-modal').classList.add('hidden');
    window.showNotification('success', 'Benefit program created successfully.');
}
</script>
@endsection
