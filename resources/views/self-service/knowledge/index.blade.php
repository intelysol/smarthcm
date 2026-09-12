@extends('self-service.layout')

@section('title', 'Knowledge Base & FAQs')

@section('content')
<div class="space-y-8">
    <div class="text-center max-w-2xl mx-auto space-y-4">
        <h1 class="text-3xl font-bold text-white tracking-tight">How can HR help you today?</h1>
        <p class="text-slate-400 text-sm">Search policies, guidelines, leave rules, payroll deduction explanations, and frequently asked questions.</p>

        <form action="{{ route('self-service.knowledge.index') }}" method="GET" class="relative max-w-lg mx-auto">
            <input type="text" name="search" value="{{ $query ?? '' }}" placeholder="Search articles, keywords, topics (e.g. visa letter, leave balance, tax)..." class="w-full pl-10 pr-4 py-3 bg-slate-900 border border-slate-800 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-teal-500 shadow-lg">
            <div class="absolute left-3.5 top-3.5 text-slate-500">
                <i class="fa-solid fa-magnifying-glass"></i>
            </div>
        </form>
    </div>

    <!-- Article List -->
    <div class="max-w-4xl mx-auto space-y-4">
        <h2 class="text-base font-bold text-white">{{ !empty($query) ? "Search Results for '{$query}'" : 'Popular HR Articles' }}</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @forelse($articles as $art)
            <a href="{{ route('self-service.knowledge.show', $art) }}" class="bg-slate-900 border border-slate-800 hover:border-teal-500/50 rounded-xl p-5 transition group">
                <div class="flex items-center justify-between text-xs text-slate-400 mb-2">
                    <span class="text-teal-400 font-medium">{{ $art->category?->name ?? 'Policy Guide' }}</span>
                    <span><i class="fa-regular fa-eye mr-1"></i> {{ $art->views_count }} views</span>
                </div>
                <h3 class="text-base font-bold text-white group-hover:text-teal-300 transition">{{ $art->title }}</h3>
                <p class="text-xs text-slate-400 mt-1 line-clamp-2">{{ $art->summary ?? substr($art->content, 0, 100) }}</p>
            </a>
            @empty
            <div class="col-span-2 text-center py-8 text-sm text-slate-500">No articles matching your search criteria.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
