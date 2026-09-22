@extends('public.layouts.marketing')

@section('schema')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "BlogPosting",
    "headline": "{{ addslashes($article['title']) }}",
    "description": "{{ addslashes($article['summary']) }}",
    "author": {
        "@@type": "Organization",
        "name": "{{ addslashes($article['author']) }}"
    },
    "publisher": {
        "@@type": "Organization",
        "name": "SmartHCM",
        "logo": "{{ asset('images/logo.png') }}"
    },
    "datePublished": "{{ $article['published_at'] }}",
    "dateModified": "{{ $article['updated_at'] }}",
    "mainEntityOfPage": {
        "@@type": "WebPage",
        "@@id": "{{ $canonical }}"
    }
}
</script>
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "BreadcrumbList",
    "itemListElement": [
        {
            "@@type": "ListItem",
            "position": 1,
            "name": "Home",
            "item": "{{ url('/') }}"
        },
        {
            "@@type": "ListItem",
            "position": 2,
            "name": "Blog",
            "item": "{{ url('/blog') }}"
        },
        {
            "@@type": "ListItem",
            "position": 3,
            "name": "{{ addslashes($article['title']) }}",
            "item": "{{ $canonical }}"
        }
    ]
}
</script>
@endsection

@section('content')

<!-- Header -->
<section class="bg-[#1E3A5F] text-white py-16 px-4 sm:px-6 lg:px-8 border-b border-[#142A44]">
    <div class="max-w-4xl mx-auto space-y-6">
        <nav class="text-xs text-slate-300 flex items-center space-x-2" aria-label="Breadcrumb">
            <a href="{{ url('/') }}" class="hover:text-white">Home</a>
            <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
            <a href="{{ url('/blog') }}" class="hover:text-white">Blog</a>
            <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
            <span class="text-[#F4E7B2] font-semibold">{{ $article['category'] }}</span>
        </nav>

        <h1 class="text-2xl sm:text-4xl lg:text-5xl font-black tracking-tight text-white leading-tight">
            {{ $article['title'] }}
        </h1>

        <!-- E-E-A-T Author & Metadata Block -->
        <div class="flex flex-wrap items-center gap-4 text-xs text-slate-300 pt-2 border-t border-slate-700">
            <div class="flex items-center space-x-2">
                <i class="fa-solid fa-user-pen text-[#C9A227]"></i>
                <span class="font-semibold text-white">{{ $article['author'] }}</span>
            </div>
            <span>&bull;</span>
            <div>Published: {{ $article['published_at'] }}</div>
            <span>&bull;</span>
            <div>Updated: {{ $article['updated_at'] }}</div>
            <span>&bull;</span>
            <div>{{ $article['read_time'] }}</div>
        </div>
    </div>
</section>

<!-- Content -->
<section class="py-16 px-4 sm:px-6 lg:px-8 bg-white">
    <div class="max-w-4xl mx-auto space-y-10">
        <!-- Executive Summary -->
        <div class="p-6 rounded-2xl bg-[#F7F9FC] border border-slate-200 text-xs sm:text-sm text-slate-700 leading-relaxed font-medium">
            <strong class="text-slate-900 block font-bold mb-1">Executive Summary:</strong>
            {{ $article['summary'] }}
        </div>

        <!-- Main Body -->
        <div class="prose prose-sm text-slate-700 max-w-none text-xs sm:text-sm leading-relaxed space-y-4">
            <p>{{ $article['content'] }}</p>

            <h3 class="text-base font-bold text-[#1E3A5F] pt-4">Strategic &amp; Technical Takeaways</h3>
            <ul class="space-y-2 pt-2">
                @foreach($article['key_takeaways'] as $point)
                    <li class="flex items-start">
                        <i class="fa-solid fa-check-circle text-[#16805C] mr-2.5 mt-0.5 text-xs"></i>
                        <span>{{ $point }}</span>
                    </li>
                @endforeach
            </ul>
        </div>

        <!-- Call to Action Banner -->
        <div class="mt-12 p-8 rounded-2xl bg-gradient-to-r from-[#1E3A5F] to-[#142A44] text-white flex flex-col sm:flex-row items-center justify-between gap-6 shadow-lg">
            <div class="space-y-1">
                <h3 class="text-base font-bold">Ready to Put These Insights into Production?</h3>
                <p class="text-xs text-slate-300">Schedule an enterprise consultation to evaluate SmartHCM live.</p>
            </div>
            <a href="{{ url('/demo') }}" class="px-6 py-3 rounded-xl bg-[#C9A227] hover:bg-[#B08E20] text-[#1E3A5F] font-bold text-xs shadow shrink-0">
                Request a Demo
            </a>
        </div>
    </div>
</section>

@endsection
