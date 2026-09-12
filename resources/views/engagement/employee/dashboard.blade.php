@extends('layouts.engagement')

@section('title', 'My Engagement & Sentiment')

@section('content')
<div class="space-y-8">
    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-slate-800 to-slate-800/80 rounded-2xl p-6 border border-slate-700/60 shadow-xl flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-sparkles text-pink-400"></i> Your Voice Matters
            </h1>
            <p class="text-slate-400 text-sm mt-1">Participate in anonymous and confidential surveys, share continuous improvement ideas, and celebrate colleagues.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('engagement.employee.pulse') }}" class="px-4 py-2 bg-gradient-to-r from-pink-500 to-rose-500 hover:from-pink-600 hover:to-rose-600 text-white text-sm font-semibold rounded-xl shadow-lg shadow-pink-500/25 transition">
                <i class="fa-solid fa-bolt mr-1.5"></i> Quick Pulse Check
            </a>
        </div>
    </div>

    <!-- Active Surveys Section -->
    <div>
        <h2 class="text-lg font-semibold text-slate-200 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-clipboard-list text-pink-400"></i> Active Surveys & Campaigns
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($activeSurveys as $camp)
            <div class="bg-slate-800/60 border border-slate-700/60 rounded-xl p-5 hover:border-pink-500/40 transition flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-start mb-2">
                        <span class="px-2 py-0.5 rounded text-xs font-semibold {{ $camp->survey?->confidentiality_type === 'anonymous' ? 'bg-indigo-500/20 text-indigo-300 border border-indigo-500/30' : 'bg-emerald-500/20 text-emerald-300' }}">
                            {{ ucfirst($camp->survey?->confidentiality_type ?? 'anonymous') }}
                        </span>
                        <span class="text-xs text-slate-400"><i class="fa-regular fa-clock mr-1"></i> Due {{ $camp->end_date?->format('M d, Y') }}</span>
                    </div>
                    <h3 class="font-bold text-white text-base">{{ $camp->name }}</h3>
                    <p class="text-xs text-slate-400 mt-1 line-clamp-2">{{ $camp->description ?? 'Your confidential participation helps improve organizational culture.' }}</p>
                </div>
                <div class="mt-6 flex justify-between items-center pt-4 border-t border-slate-700/40">
                    <span class="text-xs text-slate-400">{{ $camp->survey?->questions?->count() ?? 5 }} questions</span>
                    <a href="{{ route('engagement.employee.take', $camp->id) }}" class="px-3 py-1.5 bg-pink-500/10 hover:bg-pink-500/20 text-pink-400 border border-pink-500/30 text-xs font-semibold rounded-lg transition">
                        Take Survey <i class="fa-solid fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
            @empty
            <div class="col-span-full bg-slate-800/30 border border-slate-700/40 rounded-xl p-8 text-center text-slate-400">
                <i class="fa-solid fa-circle-check text-emerald-400 text-3xl mb-2"></i>
                <p>You have no pending surveys at this time. All caught up!</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Recognition & Ideas Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Recent Kudos -->
        <div class="bg-slate-800/40 border border-slate-700/60 rounded-2xl p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                    <i class="fa-solid fa-award text-amber-400"></i> Recognition Wall
                </h2>
                <a href="{{ route('engagement.employee.recognition') }}" class="text-xs text-pink-400 hover:text-pink-300">View All &rarr;</a>
            </div>
            <div class="space-y-4">
                @forelse($recognitions as $rec)
                <div class="p-3.5 bg-slate-800/70 border border-slate-700/50 rounded-xl">
                    <div class="flex justify-between items-start">
                        <span class="text-xs font-medium text-slate-300">
                            <strong>{{ $rec->sender?->first_name }}</strong> recognized <strong>{{ $rec->recipient?->first_name }}</strong>
                        </span>
                        @if($rec->value_tag)
                        <span class="px-2 py-0.5 rounded text-xs bg-amber-500/10 text-amber-300 border border-amber-500/20">{{ $rec->value_tag }}</span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-400 mt-1 italic">"{{ $rec->message }}"</p>
                </div>
                @empty
                <p class="text-xs text-slate-400 text-center py-4">No recent recognition posts yet.</p>
                @endforelse
            </div>
        </div>

        <!-- Community Ideas -->
        <div class="bg-slate-800/40 border border-slate-700/60 rounded-2xl p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                    <i class="fa-solid fa-lightbulb text-yellow-400"></i> Ideas & Innovation Box
                </h2>
                <a href="{{ route('engagement.employee.suggestions') }}" class="text-xs text-pink-400 hover:text-pink-300">Submit Idea &rarr;</a>
            </div>
            <div class="space-y-4">
                @forelse($suggestions as $sug)
                <div class="p-3.5 bg-slate-800/70 border border-slate-700/50 rounded-xl flex justify-between items-center">
                    <div>
                        <span class="px-2 py-0.5 rounded text-xs bg-slate-700 text-slate-300 uppercase font-semibold mr-1.5">{{ $sug->category }}</span>
                        <span class="text-xs font-medium text-white">{{ $sug->title }}</span>
                        <p class="text-xs text-slate-400 mt-1 line-clamp-1">{{ $sug->description }}</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-lg bg-pink-500/10 text-pink-400 border border-pink-500/20 text-xs font-bold">
                        <i class="fa-solid fa-thumbs-up mr-1"></i> {{ $sug->votes_count }}
                    </span>
                </div>
                @empty
                <p class="text-xs text-slate-400 text-center py-4">No suggestions submitted yet. Be the first to share an idea!</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
