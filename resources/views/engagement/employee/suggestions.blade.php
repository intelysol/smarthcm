@extends('layouts.engagement')

@section('title', 'Employee Suggestions & Ideas')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-slate-800/80 p-6 rounded-2xl border border-slate-700/60 shadow-xl">
        <div>
            <h1 class="text-2xl font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-lightbulb text-yellow-400"></i> Suggestions & Innovation Box
            </h1>
            <p class="text-slate-400 text-sm mt-1">Submit innovative ideas for organizational, operational, and cultural improvements.</p>
        </div>
        <button onclick="document.getElementById('suggestionModal').classList.remove('hidden')" class="px-4 py-2 bg-gradient-to-r from-pink-500 to-rose-500 hover:from-pink-600 hover:to-rose-600 text-white text-xs font-bold rounded-xl shadow-lg shadow-pink-500/25 transition">
            <i class="fa-solid fa-plus mr-1.5"></i> Submit New Idea
        </button>
    </div>

    <!-- Suggestion Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($suggestions as $sug)
        <div class="bg-slate-800/60 border border-slate-700/60 rounded-2xl p-6 hover:border-pink-500/40 transition flex flex-col justify-between shadow-lg">
            <div>
                <div class="flex justify-between items-start mb-2">
                    <span class="px-2 py-0.5 rounded text-xs font-semibold bg-slate-700 text-slate-300 uppercase">
                        {{ $sug->category }}
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $sug->status === 'implemented' ? 'bg-emerald-500/20 text-emerald-300' : ($sug->status === 'accepted' ? 'bg-indigo-500/20 text-indigo-300' : 'bg-amber-500/20 text-amber-300') }}">
                        {{ ucfirst(str_replace('_', ' ', $sug->status)) }}
                    </span>
                </div>

                <h3 class="font-bold text-white text-base mt-2">{{ $sug->title }}</h3>
                <p class="text-xs text-slate-400 mt-2">{{ $sug->description }}</p>

                @if($sug->review_notes)
                <div class="mt-4 p-3 bg-slate-900/60 rounded-xl border border-slate-700/50 text-xs text-emerald-300">
                    <strong>HR / Committee Review:</strong> {{ $sug->review_notes }}
                </div>
                @endif
            </div>

            <div class="flex justify-between items-center pt-4 border-t border-slate-700/40 mt-4 text-xs text-slate-400">
                <span>By: <strong class="text-slate-300">{{ $sug->is_anonymous ? 'Anonymous' : ($sug->employee ? "{$sug->employee->first_name} {$sug->employee->last_name}" : 'Employee') }}</strong></span>

                <form action="{{ url('/api/v1/hcm/me/engagement/suggestions/' . $sug->id . '/vote') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-pink-500/10 hover:bg-pink-500/20 text-pink-400 border border-pink-500/30 font-bold transition flex items-center gap-1.5">
                        <i class="fa-solid fa-thumbs-up"></i> {{ $sug->votes_count }} Votes
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="col-span-full bg-slate-800/30 border border-slate-700/40 rounded-xl p-12 text-center text-slate-400">
            <i class="fa-solid fa-lightbulb text-yellow-400 text-3xl mb-2"></i>
            <p>No ideas submitted yet. Be the first to suggest an enhancement!</p>
        </div>
        @endforelse
    </div>
</div>

<!-- Suggestion Modal -->
<div id="suggestionModal" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-800 border border-slate-700 rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4">
        <div class="flex justify-between items-center">
            <h3 class="text-base font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-lightbulb text-yellow-400"></i> Propose Improvement Idea
            </h3>
            <button onclick="document.getElementById('suggestionModal').classList.add('hidden')" class="text-slate-400 hover:text-white">&times;</button>
        </div>

        <form action="{{ url('/api/v1/hcm/me/engagement/suggestions') }}" method="POST" class="space-y-4 text-xs">
            @csrf
            <div>
                <label class="block text-slate-300 font-medium mb-1">Category *</label>
                <select name="category" class="w-full bg-slate-900 border border-slate-700 rounded-xl p-2.5 text-white focus:outline-none focus:border-pink-500">
                    <option value="Workplace Culture">Workplace Culture</option>
                    <option value="Process & Productivity">Process & Productivity</option>
                    <option value="Wellbeing & Health">Wellbeing & Health</option>
                    <option value="Tools & Technology">Tools & Technology</option>
                    <option value="Customer Experience">Customer Experience</option>
                </select>
            </div>

            <div>
                <label class="block text-slate-300 font-medium mb-1">Idea Title *</label>
                <input type="text" name="title" placeholder="e.g. Introduce No-Meeting Focus Fridays" class="w-full bg-slate-900 border border-slate-700 rounded-xl p-2.5 text-white focus:outline-none focus:border-pink-500" required>
            </div>

            <div>
                <label class="block text-slate-300 font-medium mb-1">Description & Expected Impact *</label>
                <textarea name="description" rows="3" placeholder="Explain how this idea works and what benefits it brings..." class="w-full bg-slate-900 border border-slate-700 rounded-xl p-2.5 text-white focus:outline-none focus:border-pink-500" required></textarea>
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_anonymous" value="1" id="anonCheck" class="accent-pink-500 rounded">
                <label for="anonCheck" class="text-slate-300 cursor-pointer">Submit anonymously (hide my name from peers and managers)</label>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('suggestionModal').classList.add('hidden')" class="px-4 py-2 bg-slate-700 text-slate-300 rounded-xl font-semibold">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-pink-500 hover:bg-pink-600 text-white font-bold rounded-xl shadow-lg shadow-pink-500/20">Submit Idea</button>
            </div>
        </form>
    </div>
</div>
@endsection
