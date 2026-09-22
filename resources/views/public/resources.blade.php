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
            "name": "Resources",
            "item": "{{ url('/resources') }}"
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
            <span class="text-[#F4E7B2] font-semibold">Resources</span>
        </nav>
        <h1 class="text-3xl sm:text-5xl font-black tracking-tight text-white">
            SmartHCM Knowledge &amp; Resource Center
        </h1>
        <p class="text-sm sm:text-base text-slate-200 max-w-3xl leading-relaxed">
            Authoritative technical documentation, workforce management research, entity-first HCM definitions, and frequently asked questions.
        </p>
    </div>
</section>

<section class="py-16 px-4 sm:px-6 lg:px-8 bg-[#F7F9FC]">
    <div class="max-w-7xl mx-auto space-y-12">
        <!-- 4 Primary Resource Portals -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <a href="{{ url('/docs') }}" class="p-6 rounded-2xl bg-white border border-slate-200 shadow-sm hover:border-[#1E3A5F] transition space-y-3 block">
                <div class="w-10 h-10 rounded-xl bg-[#1E3A5F]/10 text-[#1E3A5F] flex items-center justify-center text-lg">
                    <i class="fa-solid fa-book-bookmark"></i>
                </div>
                <h2 class="text-base font-bold text-[#1E3A5F]">Product Documentation</h2>
                <p class="text-xs text-slate-600 leading-relaxed">Architectural guides, setup tutorials, REST APIs, and administrative manuals.</p>
                <div class="text-xs font-bold text-[#1E3A5F] pt-2">Browse Docs &rarr;</div>
            </a>

            <a href="{{ url('/glossary') }}" class="p-6 rounded-2xl bg-white border border-slate-200 shadow-sm hover:border-[#1E3A5F] transition space-y-3 block">
                <div class="w-10 h-10 rounded-xl bg-[#C9A227]/20 text-[#B08E20] flex items-center justify-center text-lg">
                    <i class="fa-solid fa-spell-check"></i>
                </div>
                <h2 class="text-base font-bold text-[#1E3A5F]">HCM Glossary</h2>
                <p class="text-xs text-slate-600 leading-relaxed">Factual entity definitions of HCM, HRIS, HRMS, WFM, Geofencing, and more.</p>
                <div class="text-xs font-bold text-[#1E3A5F] pt-2">Explore Terms &rarr;</div>
            </a>

            <a href="{{ url('/faq') }}" class="p-6 rounded-2xl bg-white border border-slate-200 shadow-sm hover:border-[#1E3A5F] transition space-y-3 block">
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-circle-question"></i>
                </div>
                <h2 class="text-base font-bold text-[#1E3A5F]">FAQ Directory</h2>
                <p class="text-xs text-slate-600 leading-relaxed">Categorized answers on attendance tracking, payroll, security, and implementation.</p>
                <div class="text-xs font-bold text-[#1E3A5F] pt-2">Search FAQs &rarr;</div>
            </a>

            <a href="{{ url('/blog') }}" class="p-6 rounded-2xl bg-white border border-slate-200 shadow-sm hover:border-[#1E3A5F] transition space-y-3 block">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-newspaper"></i>
                </div>
                <h2 class="text-base font-bold text-[#1E3A5F]">Workforce Insights</h2>
                <p class="text-xs text-slate-600 leading-relaxed">Technical articles on mobile attendance, payroll automation, and self-service ROI.</p>
                <div class="text-xs font-bold text-[#1E3A5F] pt-2">Read Articles &rarr;</div>
            </a>
        </div>

        <!-- Featured Technical Documentation Chapters -->
        <div class="space-y-6 pt-6">
            <h2 class="text-xl font-bold text-[#1E3A5F]">Featured Documentation Chapters</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($docs as $d)
                    <a href="{{ url('/docs/' . $d['slug']) }}" class="p-6 rounded-2xl bg-white border border-slate-200 shadow-sm hover:border-[#C9A227] transition space-y-2 block">
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-700 uppercase">{{ $d['category'] }}</span>
                        <h3 class="text-sm font-bold text-[#1E3A5F]">{{ $d['title'] }}</h3>
                        <p class="text-xs text-slate-600 leading-relaxed">{{ $d['summary'] }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</section>

@endsection
