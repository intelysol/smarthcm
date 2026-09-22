@extends('public.layouts.marketing')

@section('schema')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "Article",
    "headline": "{{ addslashes($doc['title']) }}",
    "description": "{{ addslashes($doc['summary']) }}",
    "author": {
        "@@type": "Organization",
        "name": "SmartHCM Engineering & Technical Documentation"
    },
    "publisher": {
        "@@type": "Organization",
        "name": "SmartHCM",
        "logo": "{{ asset('images/logo.png') }}"
    },
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
            "name": "Documentation",
            "item": "{{ url('/docs') }}"
        },
        {
            "@@type": "ListItem",
            "position": 3,
            "name": "{{ addslashes($doc['title']) }}",
            "item": "{{ $canonical }}"
        }
    ]
}
</script>
@endsection

@section('content')

<section class="bg-[#1E3A5F] text-white py-12 px-4 sm:px-6 lg:px-8 border-b border-[#142A44]">
    <div class="max-w-7xl mx-auto space-y-3">
        <nav class="text-xs text-slate-300 flex items-center space-x-2" aria-label="Breadcrumb">
            <a href="{{ url('/') }}" class="hover:text-white">Home</a>
            <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
            <a href="{{ url('/resources') }}" class="hover:text-white">Resources</a>
            <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
            <span class="text-[#F4E7B2] font-semibold">Documentation</span>
        </nav>
        <h1 class="text-2xl sm:text-4xl font-black tracking-tight text-white">
            SmartHCM Public Product Documentation
        </h1>
        <p class="text-xs sm:text-sm text-slate-200">
            Authoritative technical guides, configuration walkthroughs, and API references.
        </p>
    </div>
</section>

<section class="py-12 px-4 sm:px-6 lg:px-8 bg-[#F7F9FC]">
    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            <!-- Sidebar Navigation -->
            <aside class="lg:col-span-4 bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4 sticky top-28">
                <h2 class="text-xs font-bold uppercase tracking-wider text-[#1E3A5F]">Documentation Chapters</h2>
                <nav class="space-y-1 text-xs font-medium">
                    @foreach($allDocs as $slugKey => $dItem)
                        <a href="{{ url('/docs/' . $slugKey) }}" class="flex items-center justify-between px-3 py-2 rounded-lg transition {{ $slugKey === $doc['slug'] ? 'bg-[#1E3A5F] text-white font-bold' : 'text-slate-700 hover:bg-slate-100' }}">
                            <span>{{ $dItem['title'] }}</span>
                            <i class="fa-solid fa-chevron-right text-[9px] opacity-70"></i>
                        </a>
                    @endforeach
                </nav>

                <div class="pt-4 border-t border-slate-100">
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 text-[11px] text-slate-600 space-y-2">
                        <strong class="text-slate-900 block font-semibold"><i class="fa-solid fa-lock text-[#C9A227] mr-1"></i> Public Spec Guard</strong>
                        <span>Public documentation never exposes client tokens, production secrets, or private database credentials.</span>
                    </div>
                </div>
            </aside>

            <!-- Main Documentation Content -->
            <article class="lg:col-span-8 bg-white rounded-2xl border border-slate-200 p-8 sm:p-10 shadow-sm space-y-8">
                <div class="border-b border-slate-200 pb-6 space-y-2">
                    <span class="text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-[#C9A227]/20 text-[#B08E20] uppercase tracking-wider">
                        {{ $doc['category'] }}
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-black text-[#1E3A5F]">{{ $doc['title'] }}</h2>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">{{ $doc['summary'] }}</p>
                </div>

                <div class="prose prose-sm text-slate-700 max-w-none space-y-4 text-xs sm:text-sm leading-relaxed">
                    <p>{{ $doc['content'] }}</p>

                    <h3 class="text-base font-bold text-[#1E3A5F] pt-4">Operational Architecture Sections</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                        @foreach($doc['sections'] as $sec)
                            <div class="p-3.5 rounded-xl bg-[#F7F9FC] border border-slate-200 flex items-center space-x-2 text-xs font-semibold text-slate-800">
                                <i class="fa-solid fa-file-lines text-[#1E3A5F]"></i>
                                <span>{{ $sec }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="pt-8 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-4">
                    <span>Published by SmartHCM Technical Documentation Team</span>
                    <a href="{{ url('/demo') }}" class="font-bold text-[#1E3A5F] hover:text-[#C9A227]">Need setup assistance? Request a Demo &rarr;</a>
                </div>
            </article>
        </div>
    </div>
</section>

@endsection
