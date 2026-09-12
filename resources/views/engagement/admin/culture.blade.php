@extends('layouts.engagement')

@section('title', 'Organizational Culture Initiatives & Goals')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-slate-800/80 p-6 rounded-2xl border border-slate-700/60 shadow-xl">
        <div>
            <h1 class="text-2xl font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-seedling text-emerald-400"></i> Culture Initiatives & Goals
            </h1>
            <p class="text-slate-400 text-sm mt-1">Drive strategic wellbeing, inclusion, and leadership culture programs across the enterprise.</p>
        </div>
        <button onclick="document.getElementById('newInitiativeModal').classList.remove('hidden')" class="px-4 py-2 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white text-xs font-bold rounded-xl shadow-lg shadow-emerald-500/25 transition">
            <i class="fa-solid fa-plus mr-1.5"></i> New Culture Initiative
        </button>
    </div>

    <!-- Initiatives Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($initiatives as $init)
        <div class="bg-slate-800/60 border border-slate-700/60 rounded-2xl p-6 hover:border-emerald-500/40 transition flex flex-col justify-between shadow-lg">
            <div>
                <div class="flex justify-between items-start">
                    <span class="px-2 py-0.5 rounded text-xs font-semibold bg-emerald-500/10 text-emerald-300 border border-emerald-500/20">
                        {{ ucfirst($init->category) }}
                    </span>
                    <span class="px-2 py-0.5 rounded text-xs font-semibold {{ $init->status === 'completed' ? 'bg-emerald-500/20 text-emerald-300' : 'bg-slate-700 text-slate-300' }}">
                        {{ ucfirst($init->status) }}
                    </span>
                </div>

                <h3 class="font-bold text-white text-base mt-2">{{ $init->title }}</h3>
                <p class="text-xs text-slate-400 mt-1">{{ $init->description }}</p>

                <div class="mt-4 pt-3 border-t border-slate-700/40 flex justify-between text-xs text-slate-300">
                    <span>Actions: <strong>{{ $init->actions_count ?? 0 }}</strong></span>
                    <span>Reached: <strong>{{ $init->employees_reached }}</strong></span>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-700/40 mt-4 flex justify-between items-center text-xs text-slate-400">
                <span>{{ $init->start_date?->format('M d') }} - {{ $init->end_date?->format('M d, Y') ?? 'Ongoing' }}</span>
                <span>{{ $init->owner ? "{$init->owner->first_name} {$init->owner->last_name}" : 'HR Team' }}</span>
            </div>
        </div>
        @empty
        <div class="col-span-full bg-slate-800/30 border border-slate-700/40 rounded-xl p-12 text-center text-slate-400">
            <i class="fa-solid fa-seedling text-emerald-400 text-3xl mb-2"></i>
            <p>No culture initiatives defined yet. Create your first initiative!</p>
        </div>
        @endforelse
    </div>
</div>

<!-- New Initiative Modal -->
<div id="newInitiativeModal" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-800 border border-slate-700 rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4 text-xs">
        <div class="flex justify-between items-center">
            <h3 class="text-base font-bold text-white">Create Culture Initiative</h3>
            <button onclick="document.getElementById('newInitiativeModal').classList.add('hidden')" class="text-slate-400 hover:text-white">&times;</button>
        </div>

        <form action="{{ url('/api/v1/hcm/engagement/culture/initiatives') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-slate-300 font-medium mb-1">Code *</label>
                <input type="text" name="code" placeholder="e.g. CULT-WELL-2026" class="w-full bg-slate-900 border border-slate-700 rounded-xl p-2.5 text-white focus:outline-none focus:border-emerald-500" required>
            </div>

            <div>
                <label class="block text-slate-300 font-medium mb-1">Title *</label>
                <input type="text" name="title" placeholder="e.g. Enterprise Wellbeing & Mental Health Program" class="w-full bg-slate-900 border border-slate-700 rounded-xl p-2.5 text-white focus:outline-none focus:border-emerald-500" required>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-300 font-medium mb-1">Category *</label>
                    <select name="category" class="w-full bg-slate-900 border border-slate-700 rounded-xl p-2.5 text-white focus:outline-none focus:border-emerald-500">
                        <option value="wellbeing">Wellbeing</option>
                        <option value="inclusion">Diversity & Inclusion</option>
                        <option value="leadership">Leadership Communication</option>
                        <option value="recognition">Recognition Program</option>
                        <option value="team_building">Team Building</option>
                    </select>
                </div>
                <div>
                    <label class="block text-slate-300 font-medium mb-1">Start Date *</label>
                    <input type="date" name="start_date" class="w-full bg-slate-900 border border-slate-700 rounded-xl p-2.5 text-white focus:outline-none focus:border-emerald-500" required>
                </div>
            </div>

            <div>
                <label class="block text-slate-300 font-medium mb-1">Description</label>
                <textarea name="description" rows="3" class="w-full bg-slate-900 border border-slate-700 rounded-xl p-2.5 text-white focus:outline-none focus:border-emerald-500"></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('newInitiativeModal').classList.add('hidden')" class="px-4 py-2 bg-slate-700 text-slate-300 rounded-xl font-semibold">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-emerald-500 hover:bg-emerald-600 text-white font-bold rounded-xl shadow-lg shadow-emerald-500/20">Launch Initiative</button>
            </div>
        </form>
    </div>
</div>
@endsection
