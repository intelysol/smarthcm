@extends('public.layouts.marketing')

@section('schema')
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
        }
    ]
}
</script>
@endsection

@section('content')

<section class="bg-[#1E3A5F] text-white py-16 px-4 sm:px-6 lg:px-8 border-b border-[#142A44]">
    <div class="max-w-7xl mx-auto space-y-4">
        <nav class="text-xs text-slate-300 flex items-center space-x-2" aria-label="Breadcrumb">
            <a href="{{ url('/') }}" class="hover:text-white">Home</a>
            <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
            <span class="text-[#F4E7B2] font-semibold">Blog</span>
        </nav>
        <h1 class="text-3xl sm:text-5xl font-black tracking-tight text-white">
            SmartHCM Workforce &amp; HR Technology Insights
        </h1>
        <p class="text-sm sm:text-base text-slate-200 max-w-3xl leading-relaxed">
            Authoritative perspectives and technical architectural analyses from the SmartHCM engineering, workforce intelligence, and compliance teams.
        </p>
    </div>
</section>

<section class="py-16 px-4 sm:px-6 lg:px-8 bg-[#F7F9FC]">
    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($articles as $post)
                <article class="p-8 rounded-2xl bg-white border border-slate-200 shadow-sm hover:border-[#1E3A5F] transition flex flex-col justify-between">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between text-[11px] text-slate-500">
                            <span class="px-2.5 py-0.5 rounded-full bg-[#1E3A5F]/10 text-[#1E3A5F] font-bold">{{ $post['category'] }}</span>
                            <span>{{ $post['read_time'] }}</span>
                        </div>

                        <h2 class="text-lg font-bold text-[#1E3A5F] leading-snug">
                            <a href="{{ url('/blog/' . $post['slug']) }}" class="hover:text-[#C9A227] transition">
                                {{ $post['title'] }}
                            </a>
                        </h2>

                        <p class="text-xs text-slate-600 leading-relaxed">{{ $post['summary'] }}</p>
                    </div>

                    <div class="pt-6 mt-6 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                        <div>
                            <span class="font-semibold text-slate-800 block">{{ $post['author'] }}</span>
                            <span class="text-[10px] text-slate-400">Updated: {{ $post['updated_at'] }}</span>
                        </div>
                        <a href="{{ url('/blog/' . $post['slug']) }}" class="font-bold text-[#1E3A5F] hover:text-[#C9A227]">
                            Read &rarr;
                        </a>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>

@endsection
