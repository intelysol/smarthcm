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
            "name": "Solutions",
            "item": "{{ url('/solutions') }}"
        },
        {
            "@@type": "ListItem",
            "position": 3,
            "name": "{{ addslashes($solution['title']) }}",
            "item": "{{ $canonical }}"
        }
    ]
}
</script>
@endsection

@section('content')

<!-- Header -->
<section class="bg-[#1E3A5F] text-white py-16 px-4 sm:px-6 lg:px-8 border-b border-[#142A44]">
    <div class="max-w-5xl mx-auto space-y-4">
        <nav class="text-xs text-slate-300 flex items-center space-x-2" aria-label="Breadcrumb">
            <a href="{{ url('/') }}" class="hover:text-white">Home</a>
            <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
            <a href="{{ url('/solutions') }}" class="hover:text-white">Solutions</a>
            <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
            <span class="text-[#F4E7B2] font-semibold">{{ $solution['title'] }}</span>
        </nav>

        <h1 class="text-3xl sm:text-5xl font-black tracking-tight text-white">
            {{ $solution['title'] }}
        </h1>
        <p class="text-sm sm:text-lg text-[#F4E7B2] font-semibold">
            {{ $solution['tagline'] }}
        </p>
    </div>
</section>

<!-- Content Model: Problem -> Operational Impact -> SmartHCM Approach -->
<section class="py-16 px-4 sm:px-6 lg:px-8 bg-white">
    <div class="max-w-5xl mx-auto space-y-12">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Problem -->
            <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                <div class="text-xs font-bold uppercase tracking-wider text-rose-600">The Problem</div>
                <h2 class="text-lg font-bold text-[#1E3A5F]">The Enterprise Pain Point</h2>
                <p class="text-xs sm:text-sm text-slate-700 leading-relaxed">{{ $solution['problem'] }}</p>
            </div>

            <!-- Operational Impact -->
            <div class="p-6 rounded-2xl bg-rose-50/50 border border-rose-100 space-y-3">
                <div class="text-xs font-bold uppercase tracking-wider text-rose-700">Operational Impact</div>
                <h2 class="text-lg font-bold text-slate-900">Cost of Inaction</h2>
                <p class="text-xs sm:text-sm text-slate-700 leading-relaxed">{{ $solution['impact'] }}</p>
            </div>
        </div>

        <!-- SmartHCM Approach -->
        <div class="p-8 rounded-2xl bg-[#F7F9FC] border border-slate-200 space-y-4">
            <div class="text-xs font-bold uppercase tracking-wider text-[#C9A227]">SmartHCM Approach</div>
            <h2 class="text-2xl font-black text-[#1E3A5F]">How SmartHCM Solves This Problem</h2>
            <p class="text-xs sm:text-sm text-slate-700 leading-relaxed">{{ $solution['approach'] }}</p>
        </div>

        <!-- Capabilities -->
        <div class="space-y-4">
            <h3 class="text-lg font-bold text-[#1E3A5F]">Key Enabling Capabilities</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                @foreach($solution['capabilities'] as $cap)
                    <div class="p-4 rounded-xl bg-white border border-slate-200 shadow-sm flex items-center space-x-3 text-xs font-semibold text-slate-800">
                        <i class="fa-solid fa-circle-check text-[#16805C]"></i>
                        <span>{{ $cap }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Workflow -->
        <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-sm space-y-3">
            <h3 class="text-sm font-bold text-[#1E3A5F]">Standard Implementation &amp; Execution Workflow</h3>
            <p class="text-xs sm:text-sm text-slate-700 font-mono bg-slate-50 p-4 rounded-xl border border-slate-100">{{ $solution['workflow'] }}</p>
        </div>

        <!-- Benefits -->
        <div class="space-y-4">
            <h3 class="text-lg font-bold text-[#1E3A5F]">Operational &amp; Financial Benefits</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                @foreach($solution['benefits'] as $benefit)
                    <div class="p-5 rounded-xl bg-emerald-50/50 border border-emerald-100 text-xs font-semibold text-emerald-900 flex items-start space-x-2">
                        <i class="fa-solid fa-arrow-trend-up text-emerald-700 mt-0.5"></i>
                        <span>{{ $benefit }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="py-16 px-4 sm:px-6 lg:px-8 bg-[#142A44] text-white text-center">
    <div class="max-w-3xl mx-auto space-y-6">
        <h2 class="text-2xl sm:text-3xl font-black">Transform {{ $solution['title'] }}</h2>
        <p class="text-xs sm:text-sm text-slate-300">
            Speak with an enterprise HCM consultant to see how this solution fits your organization.
        </p>
        <div class="flex items-center justify-center space-x-4">
            <a href="{{ url('/demo') }}" class="px-7 py-3 rounded-xl bg-[#C9A227] hover:bg-[#B08E20] text-[#1E3A5F] font-bold text-xs shadow transition">
                Request a Custom Demo
            </a>
        </div>
    </div>
</section>

@endsection
