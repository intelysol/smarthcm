@extends('layouts.engagement')

@section('title', 'Weekly Pulse Check')

@section('content')
<div class="max-w-xl mx-auto space-y-6">
    <div class="bg-gradient-to-tr from-slate-800 to-slate-800/80 border border-slate-700/60 rounded-3xl p-8 shadow-2xl text-center">
        <div class="w-14 h-14 bg-gradient-to-tr from-pink-500 to-rose-500 text-white rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-pink-500/30 text-2xl">
            <i class="fa-solid fa-bolt"></i>
        </div>

        <h1 class="text-xl font-bold text-white">How was your work experience this week?</h1>
        <p class="text-xs text-slate-400 mt-1">Lightweight, 100% anonymous sentiment pulse check.</p>

        @if($pulseCampaign)
        <form action="{{ url('/api/v1/hcm/me/engagement/surveys/' . $pulseCampaign->id . '/submit') }}" method="POST" class="mt-8 space-y-6">
            @csrf
            @php
                $firstQuestion = $pulseCampaign->survey?->questions?->first();
            @endphp

            @if($firstQuestion)
            <input type="hidden" name="answers[0][question_id]" value="{{ $firstQuestion->id }}">
            @endif

            <!-- 5-Star Sentiment Emojis -->
            <div class="grid grid-cols-5 gap-3">
                @foreach([
                    1 => ['emoji' => '😫', 'label' => 'Very Bad'],
                    2 => ['emoji' => '🙁', 'label' => 'Difficult'],
                    3 => ['emoji' => '😐', 'label' => 'Okay'],
                    4 => ['emoji' => '🙂', 'label' => 'Good'],
                    5 => ['emoji' => '🤩', 'label' => 'Amazing'],
                ] as $score => $meta)
                <label class="flex flex-col items-center p-3 bg-slate-900/60 border border-slate-700 rounded-2xl cursor-pointer hover:border-pink-500 hover:scale-105 transition">
                    <input type="radio" name="answers[0][numeric_value]" value="{{ $score }}" class="hidden peer">
                    <span class="text-3xl mb-1.5 grayscale peer-checked:grayscale-0 peer-checked:scale-125 transition">{{ $meta['emoji'] }}</span>
                    <span class="text-[10px] font-medium text-slate-400 peer-checked:text-pink-400">{{ $meta['label'] }}</span>
                </label>
                @endforeach
            </div>

            <div>
                <textarea name="answers[0][text_value]" rows="2" class="w-full bg-slate-900/70 border border-slate-700 rounded-xl p-3 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-pink-500 transition" placeholder="Optional: What is one thing that went well or could be improved?"></textarea>
            </div>

            <button type="submit" class="w-full py-3 bg-gradient-to-r from-pink-500 to-rose-500 hover:from-pink-600 hover:to-rose-600 text-white font-bold rounded-xl shadow-lg shadow-pink-500/25 text-xs transition">
                Submit Pulse Check <i class="fa-solid fa-arrow-right ml-1"></i>
            </button>
        </form>
        @else
        <div class="mt-8 p-6 bg-slate-900/50 rounded-2xl border border-slate-700 text-xs text-slate-400">
            <i class="fa-regular fa-face-smile text-emerald-400 text-2xl mb-2"></i>
            <p>No pulse survey active right now. Check back next week!</p>
        </div>
        @endif
    </div>
</div>
@endsection
