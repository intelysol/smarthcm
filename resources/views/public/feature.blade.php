@extends('public.layouts.marketing')

@section('schema')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "SoftwareApplication",
    "name": "{{ addslashes($feature['title']) }}",
    "applicationCategory": "BusinessApplication",
    "description": "{{ addslashes($feature['meta_description']) }}",
    "url": "{{ $canonical }}",
    "operatingSystem": "Web, iOS, Android"
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
            "name": "Platform",
            "item": "{{ url('/platform') }}"
        },
        {
            "@@type": "ListItem",
            "position": 3,
            "name": "{{ addslashes($feature['title']) }}",
            "item": "{{ $canonical }}"
        }
    ]
}
</script>
@if(!empty($feature['faqs']))
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "FAQPage",
    "mainEntity": [
        @foreach($feature['faqs'] as $index => $faq)
        {
            "@@type": "Question",
            "name": "{{ addslashes($faq['q']) }}",
            "acceptedAnswer": {
                "@@type": "Answer",
                "text": "{{ addslashes($faq['a']) }}"
            }
        }@if(!$loop->last),@endif
        @endforeach
    ]
}
</script>
@endif
@endsection

@section('content')

<!-- Header & Definition Hero -->
<section class="bg-[#1E3A5F] text-white py-16 px-4 sm:px-6 lg:px-8 border-b border-[#142A44]">
    <div class="max-w-7xl mx-auto space-y-6">
        <nav class="text-xs text-slate-300 flex items-center space-x-2" aria-label="Breadcrumb">
            <a href="{{ url('/') }}" class="hover:text-white">Home</a>
            <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
            <a href="{{ url('/platform') }}" class="hover:text-white">Platform</a>
            <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
            <span class="text-[#F4E7B2] font-semibold">{{ $feature['title'] }}</span>
        </nav>

        <h1 class="text-3xl sm:text-5xl font-black tracking-tight text-white">
            {{ $feature['title'] }}
        </h1>

        <!-- Short Factual Definition (AIO / GEO First) -->
        <div class="p-5 rounded-2xl bg-white/10 border border-white/20 text-xs sm:text-sm text-slate-100 leading-relaxed font-medium max-w-4xl">
            {{ $feature['definition'] }}
        </div>

        <div class="pt-2 flex flex-col sm:flex-row items-center space-y-3 sm:space-y-0 sm:space-x-4">
            <a href="{{ url('/demo') }}" class="px-7 py-3 rounded-xl bg-[#C9A227] hover:bg-[#B08E20] text-[#1E3A5F] font-bold text-xs shadow transition">
                Request a Demo
            </a>
            <a href="{{ url('/contact') }}" class="px-7 py-3 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white font-semibold text-xs transition">
                Talk to an HCM Specialist
            </a>
        </div>
    </div>
</section>

<!-- The Challenge vs. How SmartHCM Works -->
<section class="py-16 px-4 sm:px-6 lg:px-8 bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
            <!-- The Problem -->
            <div class="p-8 rounded-2xl bg-rose-50/50 border border-rose-100 space-y-4">
                <div class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 uppercase tracking-wider">
                    <i class="fa-solid fa-triangle-exclamation mr-1.5 text-rose-600"></i> The Operational Challenge
                </div>
                <h2 class="text-xl font-bold text-slate-900">Why Legacy Methods Break Down</h2>
                <p class="text-xs sm:text-sm text-slate-700 leading-relaxed">
                    {{ $feature['problem'] }}
                </p>
            </div>

            <!-- How SmartHCM Works -->
            <div class="p-8 rounded-2xl bg-emerald-50/50 border border-emerald-100 space-y-4">
                <div class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 uppercase tracking-wider">
                    <i class="fa-solid fa-circle-check mr-1.5 text-emerald-600"></i> The SmartHCM Solution
                </div>
                <h2 class="text-xl font-bold text-slate-900">How SmartHCM Automates the Workflow</h2>
                <p class="text-xs sm:text-sm text-slate-700 leading-relaxed">
                    {{ $feature['solution'] }}
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Key Capabilities -->
<section class="py-16 px-4 sm:px-6 lg:px-8 bg-[#F7F9FC]">
    <div class="max-w-7xl mx-auto space-y-10">
        <div class="max-w-3xl space-y-2">
            <span class="text-xs font-bold uppercase tracking-wider text-[#C9A227]">Functional Specifications</span>
            <h2 class="text-2xl sm:text-3xl font-black text-[#1E3A5F]">Key Capabilities</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($feature['capabilities'] as $cap)
                <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-sm flex items-start space-x-3">
                    <i class="fa-solid fa-check-circle text-[#16805C] text-base mt-0.5 shrink-0"></i>
                    <p class="text-xs sm:text-sm text-slate-700 leading-relaxed">{{ $cap }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Operational Workflow Breakdown -->
<section class="py-16 px-4 sm:px-6 lg:px-8 bg-white border-y border-slate-200">
    <div class="max-w-5xl mx-auto space-y-10">
        <div class="text-center space-y-2">
            <span class="text-xs font-bold uppercase tracking-wider text-[#C9A227]">Step-by-Step Architecture</span>
            <h2 class="text-2xl sm:text-3xl font-black text-[#1E3A5F]">End-to-End Operational Workflow</h2>
        </div>

        <div class="space-y-4">
            @foreach($feature['workflow'] as $step)
                <div class="p-6 rounded-xl bg-[#F7F9FC] border border-slate-200 flex items-start space-x-4">
                    <div class="w-8 h-8 rounded-full bg-[#1E3A5F] text-[#C9A227] font-bold text-xs flex items-center justify-center shrink-0">
                        {{ $loop->iteration }}
                    </div>
                    <div class="text-xs sm:text-sm text-slate-800 font-medium pt-1">
                        {{ $step }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Quantifiable Benefits -->
<section class="py-16 px-4 sm:px-6 lg:px-8 bg-[#F7F9FC]">
    <div class="max-w-7xl mx-auto space-y-10">
        <div class="max-w-3xl space-y-2">
            <span class="text-xs font-bold uppercase tracking-wider text-[#C9A227]">Measurable Business Impact</span>
            <h2 class="text-2xl sm:text-3xl font-black text-[#1E3A5F]">Operational Benefits</h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($feature['benefits'] as $benefit)
                <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-sm space-y-2">
                    <i class="fa-solid fa-arrow-trend-up text-[#16805C] text-lg"></i>
                    <p class="text-xs font-semibold text-slate-800 leading-relaxed">{{ $benefit }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Related Features / Internal Link Graph -->
<section class="py-16 px-4 sm:px-6 lg:px-8 bg-white border-t border-slate-200">
    <div class="max-w-7xl mx-auto space-y-8">
        <h2 class="text-xl font-bold text-[#1E3A5F]">Related Platform Modules</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($feature['related_features'] as $relatedSlug)
                @if(isset($allModules[$relatedSlug]))
                    @php $rel = $allModules[$relatedSlug]; @endphp
                    <a href="{{ url('/' . $relatedSlug) }}" class="p-5 rounded-xl bg-[#F7F9FC] border border-slate-200 hover:border-[#1E3A5F] transition block space-y-2">
                        <div class="text-xs font-bold text-[#1E3A5F] flex items-center justify-between">
                            <span>{{ $rel['name'] }}</span>
                            <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
                        </div>
                        <p class="text-[11px] text-slate-600 line-clamp-2">{{ $rel['summary'] }}</p>
                    </a>
                @endif
            @endforeach
        </div>
    </div>
</section>

<!-- Factual FAQ Section -->
@if(!empty($feature['faqs']))
<section class="py-16 px-4 sm:px-6 lg:px-8 bg-[#F7F9FC] border-t border-slate-200">
    <div class="max-w-4xl mx-auto space-y-8">
        <div class="text-center space-y-2">
            <h2 class="text-2xl font-bold text-[#1E3A5F]">Frequently Asked Questions</h2>
            <p class="text-xs text-slate-600">Technical and operational answers for {{ $feature['title'] }}.</p>
        </div>

        <div class="space-y-4">
            @foreach($feature['faqs'] as $faq)
                <div class="p-6 rounded-xl bg-white border border-slate-200 space-y-2">
                    <h3 class="text-xs sm:text-sm font-bold text-[#1E3A5F] flex items-center">
                        <i class="fa-solid fa-circle-question text-[#C9A227] mr-2"></i>
                        {{ $faq['q'] }}
                    </h3>
                    <p class="text-xs text-slate-600 leading-relaxed pl-5">{{ $faq['a'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Bottom CTA -->
<section class="py-16 px-4 sm:px-6 lg:px-8 bg-[#142A44] text-white text-center">
    <div class="max-w-3xl mx-auto space-y-6">
        <h2 class="text-2xl sm:text-3xl font-black">Experience {{ $feature['title'] }}</h2>
        <p class="text-xs sm:text-sm text-slate-300">
            Discover how this capability connects with your broader workforce and payroll operations.
        </p>
        <div class="flex flex-col sm:flex-row items-center justify-center space-y-3 sm:space-y-0 sm:space-x-4">
            <a href="{{ url('/demo') }}" class="px-8 py-3.5 rounded-xl bg-[#C9A227] hover:bg-[#B08E20] text-[#1E3A5F] font-bold text-xs shadow transition">
                Request a Demo
            </a>
            <a href="{{ url('/platform') }}" class="px-8 py-3.5 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white font-semibold text-xs transition">
                Explore Full Platform
            </a>
        </div>
    </div>
</section>

@endsection
