@extends('self-service.layout')

@section('title', $article->title)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center space-x-3 text-sm text-slate-400">
        <a href="{{ route('self-service.knowledge.index') }}" class="hover:text-white">&larr; Back to Knowledge Base</a>
        <span>/</span>
        <span class="text-teal-400">{{ $article->category?->name }}</span>
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 shadow-sm space-y-4">
        <div>
            <h1 class="text-2xl font-bold text-white">{{ $article->title }}</h1>
            <div class="flex items-center space-x-4 text-xs text-slate-400 mt-2">
                <span>Published on {{ $article->published_at?->format('F d, Y') ?? 'N/A' }}</span>
                <span>&bull;</span>
                <span><i class="fa-regular fa-eye mr-1"></i> {{ $article->views_count }} views</span>
                <span>&bull;</span>
                <span><i class="fa-regular fa-thumbs-up mr-1 text-emerald-400"></i> {{ $article->helpful_count }} found this helpful</span>
            </div>
        </div>

        @if($article->summary)
        <div class="p-4 bg-slate-850 rounded-lg border-l-4 border-teal-500 text-sm text-slate-300">
            {{ $article->summary }}
        </div>
        @endif

        <div class="prose prose-invert max-w-none text-sm text-slate-200 leading-relaxed whitespace-pre-line pt-2">
            {{ $article->content }}
        </div>

        <!-- Deflection / Service Link -->
        @if($article->service)
        <div class="mt-6 p-4 rounded-xl bg-teal-950/40 border border-teal-800/40 flex items-center justify-between">
            <div>
                <div class="text-xs font-semibold text-teal-400 uppercase tracking-wider">Related Service Available</div>
                <div class="text-sm font-bold text-white mt-0.5">{{ $article->service->name }}</div>
            </div>
            <a href="{{ route('self-service.catalog.show', $article->service) }}" class="px-4 py-2 bg-teal-600 hover:bg-teal-500 text-white text-xs font-medium rounded-lg transition">
                Submit Request Now &rarr;
            </a>
        </div>
        @endif

        <!-- Helpfulness Feedback Form -->
        <div class="pt-6 border-t border-slate-800 text-center space-y-3">
            <span class="text-xs font-semibold text-slate-400">Was this article helpful?</span>
            <div class="flex items-center justify-center space-x-3">
                <form action="{{ route('api.self-service.knowledge.feedback', $article) }}" method="POST">
                    @csrf
                    <input type="hidden" name="is_helpful" value="1">
                    <button type="submit" class="px-4 py-1.5 rounded-lg text-xs font-medium bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition flex items-center space-x-1.5">
                        <i class="fa-regular fa-thumbs-up text-emerald-400"></i>
                        <span>Yes</span>
                    </button>
                </form>

                <form action="{{ route('api.self-service.knowledge.feedback', $article) }}" method="POST">
                    @csrf
                    <input type="hidden" name="is_helpful" value="0">
                    <button type="submit" class="px-4 py-1.5 rounded-lg text-xs font-medium bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition flex items-center space-x-1.5">
                        <i class="fa-regular fa-thumbs-down text-rose-400"></i>
                        <span>No</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
