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
            "name": "Glossary",
            "item": "{{ url('/glossary') }}"
        }
    ]
}
</script>
@endsection

@section('content')

<section class="bg-[#1E3A5F] text-white py-16 px-4 sm:px-6 lg:px-8 border-b border-[#142A44]">
    <div class="max-w-4xl mx-auto space-y-4">
        <nav class="text-xs text-slate-300 flex items-center space-x-2" aria-label="Breadcrumb">
            <a href="{{ url('/') }}" class="hover:text-white">Home</a>
            <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
            <span class="text-[#F4E7B2] font-semibold">Glossary</span>
        </nav>
        <h1 class="text-3xl sm:text-5xl font-black tracking-tight text-white">
            Enterprise HCM &amp; Workforce Glossary
        </h1>
        <p class="text-sm sm:text-base text-slate-200 leading-relaxed">
            Concise, factual definitions of key human capital management, workforce planning, and operational attendance concepts, structured for clarity and entity grounding.
        </p>
    </div>
</section>

<section class="py-16 px-4 sm:px-6 lg:px-8 bg-[#F7F9FC]">
    <div class="max-w-4xl mx-auto space-y-8">
        @foreach($terms as $tKey => $term)
            <article id="{{ $tKey }}" class="p-8 rounded-2xl bg-white border border-slate-200 shadow-sm space-y-4 hover:border-[#1E3A5F] transition">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h2 class="text-xl font-bold text-[#1E3A5F]">{{ $term['term'] }}</h2>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#C9A227]/20 text-[#B08E20] font-mono">
                        {{ $term['acronym'] }}
                    </span>
                </div>

                <div class="space-y-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Authoritative Definition:</span>
                    <p class="text-xs sm:text-sm text-slate-800 leading-relaxed font-medium">{{ $term['definition'] }}</p>
                </div>

                <div class="p-4 rounded-xl bg-[#F7F9FC] border border-slate-200 text-xs space-y-1">
                    <span class="font-bold text-[#1E3A5F] block">SmartHCM Implementation Context:</span>
                    <p class="text-slate-600 leading-relaxed">{{ $term['context'] }}</p>
                </div>
            </article>
        @endforeach
    </div>
</section>

@endsection
