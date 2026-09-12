@extends('layouts.engagement')

@section('title', 'Survey Builder - ' . $survey->title)

@section('content')
<div class="space-y-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-slate-800/80 p-6 rounded-2xl border border-slate-700/60 shadow-xl">
        <div>
            <a href="{{ route('engagement.admin.surveys') }}" class="text-xs text-pink-400 hover:text-pink-300 font-semibold mb-2 inline-block">&larr; Back to Surveys</a>
            <h1 class="text-2xl font-bold text-white">{{ $survey->title }} <span class="text-xs font-mono text-slate-400 font-normal">({{ $survey->code }} - v{{ $survey->version }})</span></h1>
            <p class="text-xs text-slate-400 mt-1">{{ ucfirst($survey->survey_type) }} Survey &bull; {{ ucfirst($survey->confidentiality_type) }} &bull; Status: <strong class="text-white">{{ ucfirst($survey->status) }}</strong></p>
        </div>
        <div class="flex gap-3">
            <button onclick="document.getElementById('addQuestionModal').classList.remove('hidden')" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white text-xs font-semibold rounded-xl transition">
                <i class="fa-solid fa-plus mr-1"></i> Add Question
            </button>
            <button onclick="document.getElementById('addSectionModal').classList.remove('hidden')" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white text-xs font-semibold rounded-xl transition">
                <i class="fa-solid fa-folder-plus mr-1"></i> Add Section
            </button>
            @if($survey->status === 'draft' || $survey->status === 'review')
            <form action="{{ url('/api/v1/hcm/engagement/surveys/' . $survey->id . '/publish') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 bg-gradient-to-r from-pink-500 to-rose-500 hover:from-pink-600 hover:to-rose-600 text-white text-xs font-bold rounded-xl shadow-lg shadow-pink-500/25 transition">
                    <i class="fa-solid fa-cloud-arrow-up mr-1.5"></i> Publish & Freeze Version
                </button>
            </form>
            @endif
        </div>
    </div>

    <!-- Survey Questions Hierarchy -->
    <div class="space-y-6">
        @forelse($survey->sections ?? [] as $sec)
        <div class="bg-slate-800/40 border border-slate-700/60 rounded-2xl p-6 space-y-4">
            <div class="flex justify-between items-center border-b border-slate-700/40 pb-3">
                <div>
                    <h2 class="text-base font-bold text-white flex items-center gap-2">
                        <i class="fa-regular fa-folder-open text-pink-400"></i> {{ $sec->title }}
                    </h2>
                    @if($sec->description)
                    <p class="text-xs text-slate-400 mt-0.5">{{ $sec->description }}</p>
                    @endif
                </div>
                <span class="text-xs text-slate-400">{{ $sec->questions?->count() ?? 0 }} questions</span>
            </div>

            <div class="space-y-3">
                @foreach($sec->questions ?? [] as $q)
                <div class="p-4 bg-slate-900/60 border border-slate-700/50 rounded-xl flex justify-between items-start gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-pink-500/10 text-pink-400 uppercase">{{ $q->question_type }}</span>
                            <span class="px-2 py-0.5 rounded text-[10px] bg-slate-700 text-slate-300 font-semibold">{{ $q->dimension }}</span>
                            @if($q->is_required)
                            <span class="text-[10px] text-rose-400 font-bold">Required</span>
                            @endif
                        </div>
                        <h3 class="text-xs font-semibold text-white">{{ $q->question }}</h3>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @empty
        <div class="bg-slate-800/40 border border-slate-700/50 rounded-xl p-8 text-center text-slate-400">
            No sections defined yet. Questions added will be grouped by dimension.
        </div>
        @endforelse

        <!-- Standalone Questions -->
        @if($survey->questions->whereNull('section_id')->isNotEmpty())
        <div class="bg-slate-800/40 border border-slate-700/60 rounded-2xl p-6 space-y-4">
            <h2 class="text-base font-bold text-white border-b border-slate-700/40 pb-3">General Questions</h2>
            <div class="space-y-3">
                @foreach($survey->questions->whereNull('section_id') as $q)
                <div class="p-4 bg-slate-900/60 border border-slate-700/50 rounded-xl flex justify-between items-start gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-pink-500/10 text-pink-400 uppercase">{{ $q->question_type }}</span>
                            <span class="px-2 py-0.5 rounded text-[10px] bg-slate-700 text-slate-300 font-semibold">{{ $q->dimension }}</span>
                            @if($q->is_required)
                            <span class="text-[10px] text-rose-400 font-bold">Required</span>
                            @endif
                        </div>
                        <h3 class="text-xs font-semibold text-white">{{ $q->question }}</h3>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Add Question Modal -->
<div id="addQuestionModal" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-800 border border-slate-700 rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4 text-xs">
        <div class="flex justify-between items-center">
            <h3 class="text-base font-bold text-white">Add Question to Survey</h3>
            <button onclick="document.getElementById('addQuestionModal').classList.add('hidden')" class="text-slate-400 hover:text-white">&times;</button>
        </div>

        <form action="{{ url('/api/v1/hcm/engagement/surveys/' . $survey->id . '/questions') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-slate-300 font-medium mb-1">Section</label>
                <select name="section_id" class="w-full bg-slate-900 border border-slate-700 rounded-xl p-2.5 text-white focus:outline-none focus:border-pink-500">
                    <option value="">-- Standalone / General --</option>
                    @foreach($survey->sections ?? [] as $sec)
                    <option value="{{ $sec->id }}">{{ $sec->title }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-slate-300 font-medium mb-1">Question Text *</label>
                <input type="text" name="question" placeholder="e.g. I feel supported by my manager in my career growth." class="w-full bg-slate-900 border border-slate-700 rounded-xl p-2.5 text-white focus:outline-none focus:border-pink-500" required>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-300 font-medium mb-1">Question Type *</label>
                    <select name="question_type" class="w-full bg-slate-900 border border-slate-700 rounded-xl p-2.5 text-white focus:outline-none focus:border-pink-500">
                        <option value="likert">Likert Scale (1-5)</option>
                        <option value="nps">eNPS (0-10)</option>
                        <option value="rating">Rating (1-5)</option>
                        <option value="single_choice">Single Choice</option>
                        <option value="multiple_choice">Multiple Choice</option>
                        <option value="text">Free Text</option>
                        <option value="yes_no">Yes / No</option>
                    </select>
                </div>

                <div>
                    <label class="block text-slate-300 font-medium mb-1">Dimension *</label>
                    <select name="dimension" class="w-full bg-slate-900 border border-slate-700 rounded-xl p-2.5 text-white focus:outline-none focus:border-pink-500">
                        <option value="leadership">Leadership</option>
                        <option value="trust">Trust</option>
                        <option value="recognition">Recognition</option>
                        <option value="growth">Growth & Learning</option>
                        <option value="communication">Communication</option>
                        <option value="culture">Culture & Values</option>
                        <option value="engagement">Overall Engagement</option>
                        <option value="wellbeing">Wellbeing</option>
                    </select>
                </div>
            </div>

            <input type="hidden" name="category" value="engagement">

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('addQuestionModal').classList.add('hidden')" class="px-4 py-2 bg-slate-700 text-slate-300 rounded-xl font-semibold">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-pink-500 hover:bg-pink-600 text-white font-bold rounded-xl shadow-lg shadow-pink-500/20">Add Question</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Section Modal -->
<div id="addSectionModal" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-800 border border-slate-700 rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4 text-xs">
        <div class="flex justify-between items-center">
            <h3 class="text-base font-bold text-white">Add Section</h3>
            <button onclick="document.getElementById('addSectionModal').classList.add('hidden')" class="text-slate-400 hover:text-white">&times;</button>
        </div>

        <form action="{{ url('/api/v1/hcm/engagement/surveys/' . $survey->id . '/sections') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-slate-300 font-medium mb-1">Section Title *</label>
                <input type="text" name="title" placeholder="e.g. Leadership & Strategy" class="w-full bg-slate-900 border border-slate-700 rounded-xl p-2.5 text-white focus:outline-none focus:border-pink-500" required>
            </div>

            <div>
                <label class="block text-slate-300 font-medium mb-1">Description</label>
                <textarea name="description" rows="2" class="w-full bg-slate-900 border border-slate-700 rounded-xl p-2.5 text-white focus:outline-none focus:border-pink-500"></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('addSectionModal').classList.add('hidden')" class="px-4 py-2 bg-slate-700 text-slate-300 rounded-xl font-semibold">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-pink-500 hover:bg-pink-600 text-white font-bold rounded-xl shadow-lg shadow-pink-500/20">Create Section</button>
            </div>
        </form>
    </div>
</div>
@endsection
