@extends('layouts.engagement')

@section('title', $campaign->name)

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Survey Header -->
    <div class="bg-slate-800/80 border border-slate-700/60 rounded-2xl p-6 shadow-xl">
        <div class="flex justify-between items-start gap-4">
            <div>
                <span class="px-2.5 py-1 rounded text-xs font-semibold {{ $campaign->survey?->confidentiality_type === 'anonymous' ? 'bg-indigo-500/20 text-indigo-300 border border-indigo-500/30' : 'bg-emerald-500/20 text-emerald-300' }}">
                    <i class="fa-solid fa-user-shield mr-1"></i> {{ ucfirst($campaign->survey?->confidentiality_type ?? 'anonymous') }} Survey
                </span>
                <h1 class="text-2xl font-bold text-white mt-2">{{ $campaign->name }}</h1>
                <p class="text-xs text-slate-400 mt-1">{{ $campaign->description }}</p>
            </div>
            <span class="text-xs text-slate-400 bg-slate-900/60 px-3 py-1.5 rounded-lg border border-slate-700">
                <i class="fa-regular fa-calendar mr-1"></i> Ends {{ $campaign->end_date?->format('M d, Y') }}
            </span>
        </div>

        @if($campaign->survey?->confidentiality_type === 'anonymous')
        <div class="mt-4 p-3.5 bg-indigo-500/10 border border-indigo-500/20 rounded-xl flex items-center gap-3 text-xs text-indigo-300">
            <i class="fa-solid fa-lock text-base"></i>
            <div>
                <strong>Guaranteed Anonymity:</strong> Your responses are completely detached from your employee identity. No manager, HR administrator, or system query can link these answers to you.
            </div>
        </div>
        @endif

        @if($campaign->survey?->instructions)
        <div class="mt-4 p-3 bg-slate-900/50 rounded-xl border border-slate-700/50 text-xs text-slate-300">
            <strong>Instructions:</strong> {{ $campaign->survey->instructions }}
        </div>
        @endif
    </div>

    <!-- Questions Form -->
    <form action="{{ url('/api/v1/hcm/me/engagement/surveys/' . $campaign->id . '/submit') }}" method="POST" class="space-y-6">
        @csrf

        @forelse($campaign->survey?->questions ?? [] as $index => $question)
        <div class="bg-slate-800/50 border border-slate-700/60 rounded-xl p-6 space-y-4">
            <div class="flex items-start justify-between">
                <div class="flex gap-3">
                    <span class="w-6 h-6 rounded-full bg-pink-500/20 text-pink-400 text-xs font-bold flex items-center justify-center border border-pink-500/30">
                        {{ $index + 1 }}
                    </span>
                    <div>
                        <h2 class="text-sm font-semibold text-white">
                            {{ $question->question }}
                            @if($question->is_required)
                            <span class="text-rose-400 ml-1" title="Required">*</span>
                            @endif
                        </h2>
                        @if($question->description)
                        <p class="text-xs text-slate-400 mt-0.5">{{ $question->description }}</p>
                        @endif
                    </div>
                </div>
                <span class="px-2 py-0.5 rounded text-xs bg-slate-700/50 text-slate-400 font-medium">
                    {{ ucfirst(str_replace('_', ' ', $question->dimension ?? 'general')) }}
                </span>
            </div>

            <input type="hidden" name="answers[{{ $index }}][question_id]" value="{{ $question->id }}">

            <!-- Likert / Rating Scale -->
            @if($question->question_type === 'likert' || $question->question_type === 'rating')
            <div class="pt-2">
                <div class="flex justify-between items-center text-xs text-slate-400 px-1 mb-2">
                    <span>Strongly Disagree</span>
                    <span>Neutral</span>
                    <span>Strongly Agree</span>
                </div>
                <div class="grid grid-cols-5 gap-2">
                    @foreach([1 => '1 - Strongly Disagree', 2 => '2 - Disagree', 3 => '3 - Neutral', 4 => '4 - Agree', 5 => '5 - Strongly Agree'] as $val => $label)
                    <label class="flex flex-col items-center justify-center p-3 bg-slate-900/60 border border-slate-700 rounded-xl cursor-pointer hover:border-pink-500/60 hover:bg-slate-800 transition">
                        <input type="radio" name="answers[{{ $index }}][numeric_value]" value="{{ $val }}" class="accent-pink-500 w-4 h-4 mb-1">
                        <span class="text-xs font-bold text-slate-200">{{ $val }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            <!-- NPS (0–10) Scale -->
            @elseif($question->question_type === 'nps')
            <div class="pt-2">
                <div class="flex justify-between items-center text-xs text-slate-400 px-1 mb-2">
                    <span class="text-rose-400">0 - Not at all likely</span>
                    <span class="text-emerald-400">10 - Extremely likely</span>
                </div>
                <div class="grid grid-cols-11 gap-1.5 overflow-x-auto">
                    @for($n = 0; $n <= 10; $n++)
                    <label class="flex flex-col items-center justify-center p-2 bg-slate-900/60 border border-slate-700 rounded-lg cursor-pointer hover:border-pink-500 hover:bg-slate-800 transition">
                        <input type="radio" name="answers[{{ $index }}][numeric_value]" value="{{ $n }}" class="accent-pink-500 w-3.5 h-3.5 mb-1">
                        <span class="text-xs font-bold text-slate-200">{{ $n }}</span>
                    </label>
                    @endfor
                </div>
            </div>

            <!-- Yes / No -->
            @elseif($question->question_type === 'yes_no')
            <div class="flex gap-4 pt-2">
                <label class="flex items-center gap-2 p-3 bg-slate-900/60 border border-slate-700 rounded-xl cursor-pointer hover:border-pink-500 transition px-6">
                    <input type="radio" name="answers[{{ $index }}][numeric_value]" value="1" class="accent-pink-500">
                    <span class="text-xs font-semibold text-white">Yes</span>
                </label>
                <label class="flex items-center gap-2 p-3 bg-slate-900/60 border border-slate-700 rounded-xl cursor-pointer hover:border-pink-500 transition px-6">
                    <input type="radio" name="answers[{{ $index }}][numeric_value]" value="0" class="accent-pink-500">
                    <span class="text-xs font-semibold text-white">No</span>
                </label>
            </div>

            <!-- Free Text -->
            @elseif($question->question_type === 'text' || $question->question_type === 'long_text')
            <div class="pt-2">
                <textarea name="answers[{{ $index }}][text_value]" rows="3" class="w-full bg-slate-900/80 border border-slate-700 rounded-xl p-3 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-pink-500 transition" placeholder="Type your constructive comments here..."></textarea>
            </div>
            @endif
        </div>
        @empty
        <div class="bg-slate-800/40 border border-slate-700/50 rounded-xl p-6 text-center text-slate-400">
            No questions configured in this survey.
        </div>
        @endforelse

        <div class="flex justify-between items-center pt-4">
            <a href="{{ route('engagement.employee.dashboard') }}" class="px-4 py-2 bg-slate-800 text-slate-300 hover:bg-slate-700 rounded-xl text-xs font-semibold transition">
                <i class="fa-solid fa-arrow-left mr-1"></i> Back to Hub
            </a>
            <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-pink-500 to-rose-500 hover:from-pink-600 hover:to-rose-600 text-white text-xs font-bold rounded-xl shadow-lg shadow-pink-500/25 transition">
                <i class="fa-solid fa-paper-plane mr-1.5"></i> Submit Response
            </button>
        </div>
    </form>
</div>
@endsection
