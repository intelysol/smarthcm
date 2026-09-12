@extends('layouts.engagement')

@section('title', 'Survey Definitions & Builder')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-slate-800/80 p-6 rounded-2xl border border-slate-700/60 shadow-xl">
        <div>
            <h1 class="text-2xl font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-clipboard-question text-pink-400"></i> Survey Definitions & Templates
            </h1>
            <p class="text-slate-400 text-sm mt-1">Design questionnaires, manage question banks, configure versioning, and create recurring campaigns.</p>
        </div>
        <button onclick="document.getElementById('newSurveyModal').classList.remove('hidden')" class="px-4 py-2 bg-gradient-to-r from-pink-500 to-rose-500 hover:from-pink-600 hover:to-rose-600 text-white text-xs font-bold rounded-xl shadow-lg shadow-pink-500/25 transition">
            <i class="fa-solid fa-plus mr-1.5"></i> Create New Survey
        </button>
    </div>

    <!-- Survey Definitions Table -->
    <div class="bg-slate-800/50 border border-slate-700/60 rounded-2xl overflow-hidden shadow-xl">
        <table class="min-w-full divide-y divide-slate-700/60 text-xs text-left">
            <thead class="bg-slate-900/50 text-slate-400 uppercase font-semibold">
                <tr>
                    <th class="px-6 py-3.5">Code</th>
                    <th class="px-6 py-3.5">Title</th>
                    <th class="px-6 py-3.5">Type</th>
                    <th class="px-6 py-3.5">Confidentiality</th>
                    <th class="px-6 py-3.5">Structure</th>
                    <th class="px-6 py-3.5">Status</th>
                    <th class="px-6 py-3.5">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700/40 text-slate-200">
                @forelse($surveys as $s)
                <tr class="hover:bg-slate-800/50 transition">
                    <td class="px-6 py-4 font-mono font-bold text-pink-400">{{ $s->code }}</td>
                    <td class="px-6 py-4 font-semibold text-white">{{ $s->title }}</td>
                    <td class="px-6 py-4">{{ ucfirst($s->survey_type) }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-0.5 rounded font-semibold {{ $s->confidentiality_type === 'anonymous' ? 'bg-indigo-500/20 text-indigo-300' : 'bg-emerald-500/20 text-emerald-300' }}">
                            {{ ucfirst($s->confidentiality_type) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-slate-400">{{ $s->sections_count ?? 0 }} sections, {{ $s->questions_count ?? 0 }} questions</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-0.5 rounded font-semibold {{ $s->status === 'open' ? 'bg-emerald-500/20 text-emerald-300' : 'bg-slate-700 text-slate-300' }}">
                            {{ ucfirst($s->status) }} (v{{ $s->version }})
                        </span>
                    </td>
                    <td class="px-6 py-4 flex items-center gap-3">
                        <a href="{{ route('engagement.admin.builder', $s->id) }}" class="text-pink-400 hover:text-pink-300 font-semibold">
                            <i class="fa-solid fa-pen-ruler mr-1"></i> Builder
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-slate-400">No surveys created yet. Build your first survey definition!</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- New Survey Modal -->
<div id="newSurveyModal" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-800 border border-slate-700 rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4 text-xs">
        <div class="flex justify-between items-center">
            <h3 class="text-base font-bold text-white">Create Survey Definition</h3>
            <button onclick="document.getElementById('newSurveyModal').classList.add('hidden')" class="text-slate-400 hover:text-white">&times;</button>
        </div>

        <form action="{{ url('/api/v1/hcm/engagement/surveys') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-slate-300 font-medium mb-1">Survey Code *</label>
                <input type="text" name="code" placeholder="e.g. SURV-ENG-2026" class="w-full bg-slate-900 border border-slate-700 rounded-xl p-2.5 text-white focus:outline-none focus:border-pink-500" required>
            </div>

            <div>
                <label class="block text-slate-300 font-medium mb-1">Survey Title *</label>
                <input type="text" name="title" placeholder="e.g. Annual Employee Engagement Survey 2026" class="w-full bg-slate-900 border border-slate-700 rounded-xl p-2.5 text-white focus:outline-none focus:border-pink-500" required>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-300 font-medium mb-1">Survey Type *</label>
                    <select name="survey_type" class="w-full bg-slate-900 border border-slate-700 rounded-xl p-2.5 text-white focus:outline-none focus:border-pink-500">
                        <option value="engagement">Engagement</option>
                        <option value="pulse">Pulse</option>
                        <option value="culture">Culture</option>
                        <option value="onboarding">Onboarding</option>
                        <option value="exit">Exit</option>
                        <option value="manager_feedback">Manager Feedback</option>
                        <option value="custom">Custom</option>
                    </select>
                </div>
                <div>
                    <label class="block text-slate-300 font-medium mb-1">Confidentiality *</label>
                    <select name="confidentiality_type" class="w-full bg-slate-900 border border-slate-700 rounded-xl p-2.5 text-white focus:outline-none focus:border-pink-500">
                        <option value="anonymous">Anonymous (Guaranteed)</option>
                        <option value="confidential">Confidential (HR Only)</option>
                        <option value="named">Named</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-slate-300 font-medium mb-1">Description</label>
                <textarea name="description" rows="2" class="w-full bg-slate-900 border border-slate-700 rounded-xl p-2.5 text-white focus:outline-none focus:border-pink-500"></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('newSurveyModal').classList.add('hidden')" class="px-4 py-2 bg-slate-700 text-slate-300 rounded-xl font-semibold">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-pink-500 hover:bg-pink-600 text-white font-bold rounded-xl shadow-lg shadow-pink-500/20">Create Survey</button>
            </div>
        </form>
    </div>
</div>
@endsection
