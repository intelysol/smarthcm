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
            "name": "Industries",
            "item": "{{ url('/industries') }}"
        },
        {
            "@@type": "ListItem",
            "position": 3,
            "name": "{{ addslashes($industry['name']) }}",
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
            <a href="{{ url('/industries') }}" class="hover:text-white">Industries</a>
            <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
            <span class="text-[#F4E7B2] font-semibold">{{ $industry['name'] }}</span>
        </nav>

        <h1 class="text-3xl sm:text-5xl font-black tracking-tight text-white">
            SmartHCM for {{ $industry['name'] }}
        </h1>
        <p class="text-sm sm:text-base text-[#F4E7B2] font-semibold">
            {{ $industry['tagline'] }}
        </p>
    </div>
</section>

<!-- Industry Content Model (Prompt Section 15) -->
<section class="py-16 px-4 sm:px-6 lg:px-8 bg-white">
    <div class="max-w-5xl mx-auto space-y-12">
        <!-- Challenges & Workforce Problems -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                <div class="text-xs font-bold uppercase tracking-wider text-[#1E3A5F]">Core HR Challenges</div>
                <h2 class="text-lg font-bold text-slate-900">Unique Industry Pressures</h2>
                <p class="text-xs sm:text-sm text-slate-700 leading-relaxed">{{ $industry['challenges'] }}</p>
            </div>

            <div class="p-6 rounded-2xl bg-amber-50/50 border border-amber-100 space-y-3">
                <div class="text-xs font-bold uppercase tracking-wider text-amber-800">Operational Workforce Friction</div>
                <h2 class="text-lg font-bold text-slate-900">What Workforce Problems Occur?</h2>
                <p class="text-xs sm:text-sm text-slate-700 leading-relaxed">{{ $industry['workforce_issues'] }}</p>
            </div>
        </div>

        <!-- SmartHCM Capabilities Addressing Them -->
        <div class="space-y-4">
            <h2 class="text-xl font-bold text-[#1E3A5F]">Which SmartHCM Capabilities Address These Challenges?</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($industry['capabilities'] as $cap)
                    <div class="p-4 rounded-xl bg-[#F7F9FC] border border-slate-200 flex items-center space-x-3 text-xs font-semibold text-slate-800">
                        <i class="fa-solid fa-circle-check text-[#16805C]"></i>
                        <span>{{ $cap }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Workflows Supported & Data Involved -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="p-6 rounded-2xl bg-[#F7F9FC] border border-slate-200 space-y-3">
                <h3 class="text-sm font-bold text-[#1E3A5F]">Workflows Supported</h3>
                <p class="text-xs sm:text-sm text-slate-700 leading-relaxed">{{ $industry['workflows'] }}</p>
            </div>

            <div class="p-6 rounded-2xl bg-[#F7F9FC] border border-slate-200 space-y-3">
                <h3 class="text-sm font-bold text-[#1E3A5F]">Critical Data Entities Involved</h3>
                <p class="text-xs sm:text-sm text-slate-700 leading-relaxed font-mono bg-white p-3 rounded-lg border border-slate-200">{{ $industry['data_involved'] }}</p>
            </div>
        </div>

        <!-- How ESS and WFM Help -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="p-6 rounded-2xl bg-blue-50/40 border border-blue-100 space-y-3">
                <div class="text-xs font-bold uppercase tracking-wider text-blue-800">Employee Perspective</div>
                <h3 class="text-base font-bold text-slate-900">How Employee Self-Service (ESS) Helps</h3>
                <p class="text-xs sm:text-sm text-slate-700 leading-relaxed">{{ $industry['ess_benefit'] }}</p>
            </div>

            <div class="p-6 rounded-2xl bg-emerald-50/40 border border-emerald-100 space-y-3">
                <div class="text-xs font-bold uppercase tracking-wider text-emerald-800">Operational Leadership</div>
                <h3 class="text-base font-bold text-slate-900">How Workforce Management (WFM) Helps</h3>
                <p class="text-xs sm:text-sm text-slate-700 leading-relaxed">{{ $industry['wfm_benefit'] }}</p>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="py-16 px-4 sm:px-6 lg:px-8 bg-[#142A44] text-white text-center">
    <div class="max-w-3xl mx-auto space-y-6">
        <h2 class="text-2xl sm:text-3xl font-black">See SmartHCM for {{ $industry['name'] }}</h2>
        <p class="text-xs sm:text-sm text-slate-300">
            Request an industry-tailored demonstration with one of our enterprise workforce specialists.
        </p>
        <div class="flex items-center justify-center space-x-4">
            <a href="{{ url('/demo') }}" class="px-7 py-3 rounded-xl bg-[#C9A227] hover:bg-[#B08E20] text-[#1E3A5F] font-bold text-xs shadow transition">
                Request Industry Demo
            </a>
        </div>
    </div>
</section>

@endsection
